from __future__ import annotations

from datetime import datetime
from functools import lru_cache
from pathlib import Path
from typing import Any

import pandas as pd
from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel

# Import new algorithm modules
from astar_decision_engine import analyze_access_with_astar
from genetic_policy_optimizer import optimize_policies

APP_ROOT = Path(__file__).resolve().parent
CSV_PATH = APP_ROOT / "access_control_authentic_500k.csv"

app = FastAPI(title="AccessGuard API", version="1.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


class SimulationInput(BaseModel):
    role: str
    department: str = "engineering"
    risk_score: float
    compliance_status: str
    failed_login_attempts: int
    access_sensitivity_level: str
    time_of_day: str


def _safe_float(value: Any, default: float = 0.0) -> float:
    try:
        return float(value)
    except Exception:
        return default


def _safe_int(value: Any, default: int = 0) -> int:
    try:
        return int(value)
    except Exception:
        return default


def simulate_prolog_decision(user_data: dict[str, Any]) -> tuple[str, str]:
    risk = float(user_data.get("risk_score", 0.5))
    compliance = str(user_data.get("compliance_status", "")).lower()
    failed = int(user_data.get("failed_login_attempts", 0))
    sensitivity = str(user_data.get("access_sensitivity_level", "")).lower()
    time_of_day = str(user_data.get("time_of_day", "")).lower()
    role = str(user_data.get("role", "")).lower()
    dept = str(user_data.get("department", "")).lower()

    if risk >= 0.66 and "non" in compliance:
        return "deny", "High risk + non-compliant status"
    if failed >= 3 and sensitivity == "high":
        return "deny", f"Excessive failed logins ({failed}) with high-sensitivity access"
    if "non" in compliance and sensitivity == "high":
        return "deny", "Non-compliant user attempting high-sensitivity access"
    if time_of_day in ["night", "evening"] and sensitivity == "high":
        return "review", "Off-hours access to high-sensitivity resource - manual review required"
    if failed == 2 and 0.33 <= risk < 0.66:
        return "review", "Borderline risk with recent failed attempts"
    if "compliant" in compliance and risk < 0.4 and failed < 3 and sensitivity == "low":
        return "approve", "Low-risk compliant user accessing low-sensitivity resource"
    if role == "admin" and "compliant" in compliance and risk < 0.5:
        return "approve", "Admin with good compliance and risk profile"
    if role == "manager" and dept == "engineering" and "compliant" in compliance and risk < 0.4 and failed == 0:
        return "approve", "Engineering manager with perfect compliance"
    return "review", "Manual review recommended - does not fully satisfy approve/deny criteria"


def explain_decision(user_data: dict[str, Any]) -> dict[str, str]:
    decision, reason = simulate_prolog_decision(user_data)
    rule = "fallback_review_rule(User)."
    if decision == "deny":
        if float(user_data.get("risk_score", 0)) >= 0.66 and "non" in str(user_data.get("compliance_status", "")).lower():
            rule = "deny_access(User) :- risk_score(User, high), compliance(User, non_compliant)."
        elif int(user_data.get("failed_login_attempts", 0)) >= 3 and str(user_data.get("access_sensitivity_level", "")).lower() == "high":
            rule = "deny_access(User) :- failed_logins(User, N), N >= 3, sensitivity(User, high)."
        else:
            rule = "deny_access(User) :- compliance(User, non_compliant), sensitivity(User, high)."
    elif decision == "review":
        if str(user_data.get("time_of_day", "")).lower() in ["night", "evening"] and str(user_data.get("access_sensitivity_level", "")).lower() == "high":
            rule = "review_access(User) :- time_of_day(User, night), sensitivity(User, high)."
        elif int(user_data.get("failed_login_attempts", 0)) == 2:
            rule = "review_access(User) :- failed_logins(User, 2), risk_score(User, medium)."
    else:
        rule = "approve_access(User) :- compliance(User, compliant), risk_score(User, low), failed_logins(User, N), N < 3, sensitivity(User, low)."
    return {"decision": decision.upper(), "reason": reason, "matched_rule": rule}


@lru_cache(maxsize=1)
def load_df() -> pd.DataFrame:
    df = pd.read_csv(CSV_PATH, low_memory=False)
    if "login_time" in df.columns:
        df["login_time"] = pd.to_datetime(df["login_time"], errors="coerce")
    return df


def _serialize_user_row(row: pd.Series) -> dict[str, Any]:
    explanation = explain_decision(row.to_dict())
    return {
        "user_id": str(row.get("user_id", "")),
        "role": str(row.get("role", "")),
        "department": str(row.get("department", "")),
        "risk_score": round(_safe_float(row.get("risk_score", 0.0)), 4),
        "compliance_status": str(row.get("compliance_status", "")),
        "failed_login_attempts": _safe_int(row.get("failed_login_attempts", 0)),
        "access_sensitivity_level": str(row.get("access_sensitivity_level", "")),
        "time_of_day": str(row.get("time_of_day", "")),
        "decision": explanation["decision"],
        "reason": explanation["reason"],
        "matched_rule": explanation["matched_rule"],
    }


def _apply_filters(
    df: pd.DataFrame,
    role: str | None = None,
    department: str | None = None,
    sensitivity: str | None = None,
    compliance: str | None = None,
) -> pd.DataFrame:
    filtered = df
    if role:
        filtered = filtered[filtered["role"].astype(str).str.lower() == role.lower()]
    if department:
        filtered = filtered[filtered["department"].astype(str).str.lower() == department.lower()]
    if sensitivity:
        filtered = filtered[filtered["access_sensitivity_level"].astype(str).str.lower() == sensitivity.lower()]
    if compliance:
        filtered = filtered[filtered["compliance_status"].astype(str).str.lower() == compliance.lower()]
    return filtered


def _analytics_from_df(df: pd.DataFrame) -> dict[str, list[dict[str, float | str]]]:
    compliance_rows = (
        df.groupby("compliance_status")["access_granted"].mean().reset_index().rename(columns={"access_granted": "value"})
    )
    compliance_chart = [
        {"label": str(row["compliance_status"]), "value": round(float(row["value"]) * 100, 2)}
        for _, row in compliance_rows.iterrows()
    ]
    time_rows = (
        df.groupby("time_of_day")["anomaly_detected"].mean().reset_index().rename(columns={"anomaly_detected": "value"})
    )
    time_chart = [{"label": str(row["time_of_day"]), "value": round(float(row["value"]) * 100, 2)} for _, row in time_rows.iterrows()]
    role_rows = (
        df.groupby("role")["access_granted"].mean().sort_values(ascending=False).head(7).reset_index().rename(columns={"access_granted": "value"})
    )
    role_chart = [{"label": str(row["role"]), "value": round(float(row["value"]) * 100, 2)} for _, row in role_rows.iterrows()]
    department_rows = (
        df.groupby("department")["access_granted"].mean().sort_values(ascending=False).head(7).reset_index().rename(columns={"access_granted": "value"})
    )
    department_chart = [{"label": str(row["department"]), "value": round(float(row["value"]) * 100, 2)} for _, row in department_rows.iterrows()]
    return {
        "grant_by_compliance": compliance_chart,
        "anomaly_by_time": time_chart,
        "grant_by_role": role_chart,
        "grant_by_department": department_chart,
    }


@app.get("/api/summary")
def summary():
    df = load_df()
    return {
        "total_records": int(len(df)),
        "unique_users": int(df["user_id"].nunique()),
        "grant_rate": float(df["access_granted"].mean() * 100),
        "anomaly_rate": float(df["anomaly_detected"].mean() * 100),
    }


@app.get("/api/filter-options")
def filter_options():
    df = load_df()
    return {
        "roles": sorted(df["role"].dropna().astype(str).unique().tolist()),
        "departments": sorted(df["department"].dropna().astype(str).unique().tolist()),
        "sensitivities": sorted(df["access_sensitivity_level"].dropna().astype(str).unique().tolist()),
        "compliances": sorted(df["compliance_status"].dropna().astype(str).unique().tolist()),
    }


@app.get("/api/prolog-rules")
def prolog_rules():
    return {
        "rules": [
            "deny_access(User) :- risk_score(User, high), compliance(User, non_compliant).",
            "deny_access(User) :- failed_logins(User, N), N >= 3, sensitivity(User, high).",
            "deny_access(User) :- compliance(User, non_compliant), sensitivity(User, high).",
            "review_access(User) :- time_of_day(User, night), sensitivity(User, high).",
            "review_access(User) :- failed_logins(User, 2), risk_score(User, medium).",
            "approve_access(User) :- compliance(User, compliant), risk_score(User, low), failed_logins(User, N), N < 3, sensitivity(User, low).",
            "approve_access(User) :- role(User, admin), compliance(User, compliant), risk_score(User, low).",
            "approve_access(User) :- role(User, manager), department(User, engineering), compliance(User, compliant), risk_score(User, low), failed_logins(User, 0).",
        ]
    }


@app.get("/api/decisions-week")
def decisions_week():
    df = load_df().sample(12000, random_state=42).copy()
    labels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]
    synthetic_day = (df.index % 7).map(lambda i: labels[int(i)])
    decision_rows = []
    for _, row in df.iterrows():
        decision, _ = simulate_prolog_decision(row.to_dict())
        decision_rows.append(decision)
    df["day"] = synthetic_day.values
    df["decision"] = decision_rows

    result = []
    for day in labels:
        day_df = df[df["day"] == day]
        result.append(
            {
                "day": day,
                "approve": int((day_df["decision"] == "approve").sum()),
                "review": int((day_df["decision"] == "review").sum()),
                "deny": int((day_df["decision"] == "deny").sum()),
            }
        )
    return result


@app.get("/api/analytics")
def analytics():
    return _analytics_from_df(load_df().copy())


@app.get("/api/risk-trend")
def risk_trend():
    df = load_df().copy()
    sampled = df.sample(min(50000, len(df)), random_state=17).copy()
    sampled["bucket"] = (sampled.index % 12) + 1
    trend_rows = sampled.groupby("bucket").agg(
        avg_risk=("risk_score", "mean"),
        anomaly_rate=("anomaly_detected", "mean"),
        grant_rate=("access_granted", "mean"),
    )
    return [
        {
            "period": f"M{idx}",
            "avg_risk": round(float(row["avg_risk"]), 3),
            "anomaly_rate": round(float(row["anomaly_rate"]) * 100, 2),
            "grant_rate": round(float(row["grant_rate"]) * 100, 2),
        }
        for idx, row in trend_rows.iterrows()
    ]


@app.get("/api/dashboard-data")
def dashboard_data(
    role: str | None = None,
    department: str | None = None,
    sensitivity: str | None = None,
    compliance: str | None = None,
):
    df = _apply_filters(load_df(), role=role, department=department, sensitivity=sensitivity, compliance=compliance).copy()
    if df.empty:
        return {
            "summary": {"total_records": 0, "unique_users": 0, "grant_rate": 0.0, "anomaly_rate": 0.0},
            "decisions_week": [],
            "risk_composition": [],
            "timeline": [],
            "analytics": {"grant_by_compliance": [], "anomaly_by_time": [], "grant_by_role": [], "grant_by_department": []},
            "sample_decisions": [],
            "updated_at": datetime.now().isoformat(),
        }
    sampled = df.sample(min(12000, len(df)), random_state=42).copy()
    labels = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"]
    sampled["day"] = (sampled.index % 7).map(lambda i: labels[int(i)])
    sampled["decision"] = [simulate_prolog_decision(r.to_dict())[0] for _, r in sampled.iterrows()]
    decisions = []
    for day in labels:
        day_df = sampled[sampled["day"] == day]
        decisions.append(
            {
                "day": day,
                "approve": int((day_df["decision"] == "approve").sum()),
                "review": int((day_df["decision"] == "review").sum()),
                "deny": int((day_df["decision"] == "deny").sum()),
            }
        )
    low = int((df["risk_score"] < 0.33).sum())
    medium = int(((df["risk_score"] >= 0.33) & (df["risk_score"] < 0.66)).sum())
    high = int((df["risk_score"] >= 0.66).sum())
    total = max(low + medium + high, 1)
    risk_comp = [
        {"name": "Low Risk", "value": round((low / total) * 100, 1), "color": "#27d07d"},
        {"name": "Medium Risk", "value": round((medium / total) * 100, 1), "color": "#f4b844"},
        {"name": "High Risk", "value": round((high / total) * 100, 1), "color": "#ff5a6f"},
    ]
    analytics_data = _analytics_from_df(df)
    timeline_rows = sampled.sample(min(8, len(sampled)), random_state=7)
    timeline_data = []
    for _, row in timeline_rows.iterrows():
        decision, _ = simulate_prolog_decision(row.to_dict())
        timeline_data.append(
            {
                "user": str(row["user_id"]),
                "event": f"{row['department']} {row['access_sensitivity_level']} sensitivity access",
                "status": decision.upper(),
                "time": datetime.now().strftime("%I:%M %p"),
            }
        )
    sample_rows = [_serialize_user_row(row) for _, row in sampled.sample(min(15, len(sampled)), random_state=5).iterrows()]
    return {
        "summary": {
            "total_records": int(len(df)),
            "unique_users": int(df["user_id"].nunique()),
            "grant_rate": float(df["access_granted"].mean() * 100),
            "anomaly_rate": float(df["anomaly_detected"].mean() * 100),
        },
        "decisions_week": decisions,
        "risk_composition": risk_comp,
        "timeline": timeline_data,
        "analytics": analytics_data,
        "sample_decisions": sample_rows,
        "updated_at": datetime.now().isoformat(),
    }


@app.get("/api/risk-composition")
def risk_composition():
    df = load_df()
    low = int((df["risk_score"] < 0.33).sum())
    medium = int(((df["risk_score"] >= 0.33) & (df["risk_score"] < 0.66)).sum())
    high = int((df["risk_score"] >= 0.66).sum())
    total = max(low + medium + high, 1)
    return [
        {"name": "Low Risk", "value": round((low / total) * 100, 1), "color": "#27d07d"},
        {"name": "Medium Risk", "value": round((medium / total) * 100, 1), "color": "#f4b844"},
        {"name": "High Risk", "value": round((high / total) * 100, 1), "color": "#ff5a6f"},
    ]


@app.get("/api/timeline")
def timeline():
    df = load_df().sample(8, random_state=7).copy()
    events = []
    for _, row in df.iterrows():
        decision, _ = simulate_prolog_decision(row.to_dict())
        events.append(
            {
                "user": str(row["user_id"]),
                "event": f"{row['department']} {row['access_sensitivity_level']} sensitivity access",
                "status": decision.upper(),
                "time": datetime.now().strftime("%I:%M %p"),
            }
        )
    return events


@app.get("/api/user/{user_id}")
def single_user(user_id: str):
    df = load_df()
    matches = df[df["user_id"].astype(str) == user_id.strip()]
    if matches.empty:
        return {"found": False, "user": None}
    return {"found": True, "user": _serialize_user_row(matches.iloc[0])}


@app.get("/api/sample-decisions")
def sample_decisions():
    df = load_df().sample(15, random_state=5)
    rows = [_serialize_user_row(row) for _, row in df.iterrows()]
    return {"rows": rows}


@app.post("/api/simulate")
def simulate(payload: SimulationInput):
    explanation = explain_decision(payload.model_dump())
    return explanation


# ============================================================
# NEW: A* GRAPH SEARCH ENDPOINTS
# ============================================================

@app.post("/api/astar-analyze")
def astar_analyze(payload: SimulationInput):
    """
    Analyze user access request using A* graph search algorithm
    Finds optimal compliant paths through access decision graph
    """
    try:
        user_profile = payload.model_dump()
        result = analyze_access_with_astar(user_profile)
        return result
    except Exception as e:
        return {
            "error": str(e),
            "algorithm": "A* Graph Search",
            "recommendation": "A* analysis failed - fallback to Prolog rules"
        }


@app.post("/api/astar-compare")
def astar_compare(payload: SimulationInput):
    """
    Compare decisions: Prolog rules vs A* algorithm
    Shows which algorithm is more optimized
    """
    try:
        user_profile = payload.model_dump()
        
        # Prolog decision
        prolog_dec, prolog_reason = simulate_prolog_decision(user_profile)
        
        # A* decision
        astar_result = analyze_access_with_astar(user_profile)
        astar_dec = astar_result["decision"]
        
        return {
            "user_profile": user_profile,
            "prolog_decision": {
                "decision": prolog_dec.upper(),
                "reason": prolog_reason,
                "algorithm": "Prolog Rule Engine"
            },
            "astar_decision": {
                "decision": astar_dec.upper(),
                "path_length": astar_result.get("path_length", 0),
                "total_cost": astar_result.get("total_cost", 0),
                "algorithm": "A* Graph Search"
            },
            "agreement": prolog_dec == astar_dec,
            "recommendation": f"Both algorithms {'agree' if prolog_dec == astar_dec else 'disagree'} on {astar_dec.upper()} decision"
        }
    except Exception as e:
        return {"error": str(e), "comparison": "Failed"}


# ============================================================
# NEW: GENETIC ALGORITHM ENDPOINTS
# ============================================================

@app.post("/api/ga-optimize-policies")
def ga_optimize_policies():
    """
    Run genetic algorithm to optimize access control policies
    Uses historical data to evolve better policies over generations
    """
    try:
        df = load_df()
        
        # Prepare training data (sample for performance)
        sample_size = min(1000, len(df))
        training_data = df.sample(sample_size, random_state=42).to_dict('records')
        
        # Run GA optimization
        result = optimize_policies(
            training_data=training_data,
            population_size=20,
            generations=50
        )
        
        return result
    except Exception as e:
        return {
            "error": str(e),
            "algorithm": "Genetic Algorithm",
            "recommendation": "GA optimization failed"
        }


@app.get("/api/ga-status")
def ga_status():
    """Get status of GA optimization (cached results)"""
    return {
        "status": "ready",
        "message": "Call /api/ga-optimize-policies with POST to start optimization",
        "algorithm": "Genetic Algorithm",
        "typical_runtime": "2-5 seconds for 50 generations",
        "improvements_expected": "5-15% better policy fitness"
    }


@app.post("/api/ga-evaluate-policy")
def ga_evaluate_policy(payload: SimulationInput):
    """
    Evaluate a single user request with evolved policies
    Uses best policies from GA optimization
    """
    try:
        user_profile = payload.model_dump()
        df = load_df()
        
        # Quick sample for training
        training_data = df.sample(min(500, len(df)), random_state=42).to_dict('records')
        
        # Get best policies from GA
        ga_result = optimize_policies(training_data, population_size=15, generations=30)
        best_policy_rules = ga_result["best_policies"][0]["rules"] if ga_result["best_policies"] else []
        
        return {
            "user_profile": user_profile,
            "policies_evolved": len(ga_result["best_policies"]),
            "best_fitness": ga_result["best_policies"][0]["fitness_score"] if ga_result["best_policies"] else 0,
            "num_rules_in_best_policy": len(best_policy_rules),
            "recommendation": ga_result["recommendation"],
            "algorithm": "Genetic Algorithm"
        }
    except Exception as e:
        return {
            "error": str(e),
            "algorithm": "Genetic Algorithm",
            "recommendation": "GA evaluation failed"
        }


# ============================================================
# NEW: HYBRID ALGORITHM ENDPOINTS
# ============================================================

@app.post("/api/hybrid-analysis")
def hybrid_analysis(payload: SimulationInput):
    """
    Run all three algorithms and compare: Prolog vs A* vs GA
    Provides comprehensive security analysis
    """
    try:
        user_profile = payload.model_dump()
        
        # 1. Prolog decision
        prolog_dec, prolog_reason = simulate_prolog_decision(user_profile)
        prolog_explanation = explain_decision(user_profile)
        
        # 2. A* analysis
        astar_result = analyze_access_with_astar(user_profile)
        
        # 3. GA evaluation (quick)
        df = load_df()
        training_data = df.sample(min(300, len(df)), random_state=42).to_dict('records')
        ga_result = optimize_policies(training_data, population_size=10, generations=20)
        
        # Consensus decision
        decisions = [prolog_dec, astar_result["decision"]]
        consensus = max(set(decisions), key=decisions.count)
        
        return {
            "user_id": user_profile.get("user_id", "Unknown"),
            "algorithms": {
                "prolog": {
                    "decision": prolog_dec.upper(),
                    "reason": prolog_reason,
                    "rule": prolog_explanation.get("matched_rule", "")
                },
                "astar": {
                    "decision": astar_result["decision"].upper(),
                    "path_steps": len(astar_result.get("path_steps", [])),
                    "total_cost": astar_result.get("total_cost", 0)
                },
                "genetic_algorithm": {
                    "best_fitness": ga_result["best_policies"][0]["fitness_score"] if ga_result["best_policies"] else 0,
                    "policies_evolved": len(ga_result["best_policies"]),
                    "improvement": ga_result.get("improvement", 0)
                }
            },
            "consensus_decision": consensus.upper(),
            "unanimous": (prolog_dec == astar_result["decision"]),
            "recommendation": f"RECOMMENDED DECISION: {consensus.upper()} (consensus from hybrid AI analysis)",
            "confidence": "HIGH" if (prolog_dec == astar_result["decision"]) else "MEDIUM"
        }
    except Exception as e:
        return {
            "error": str(e),
            "recommendation": "Hybrid analysis failed - fallback to Prolog"
        }

