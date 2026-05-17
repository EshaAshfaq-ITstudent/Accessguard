import streamlit as st
import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
import seaborn as sns
from datetime import datetime
import random

# Import new algorithm modules
try:
    from astar_decision_engine import analyze_access_with_astar
    astar_available = True
except ImportError:
    astar_available = False

try:
    from genetic_policy_optimizer import optimize_policies
    ga_available = True
except ImportError:
    ga_available = False

# ============================================================
# PAGE CONFIG — must be first Streamlit call
# ============================================================

st.set_page_config(
    page_title="Smart Access Control System",
    page_icon="🔐",
    layout="wide",
    initial_sidebar_state="expanded"
)

# Custom CSS for a professional look
st.markdown("""
<style>
    .main-header {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
        padding: 20px 30px;
        border-radius: 12px;
        margin-bottom: 20px;
        text-align: center;
    }
    .main-header h1 { color: #e94560; margin: 0; font-size: 2rem; }
    .main-header p  { color: #a8b2d8; margin: 4px 0 0; font-size: 0.95rem; }

    .decision-card {
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        font-size: 1.4rem;
        font-weight: bold;
        margin: 10px 0;
    }
    .card-approve { background:#1a472a; color:#2ecc71; border:2px solid #2ecc71; }
    .card-deny    { background:#4a1122; color:#e74c3c; border:2px solid #e74c3c; }
    .card-review  { background:#4a3800; color:#f39c12; border:2px solid #f39c12; }

    .kpi-box {
        background:#1e2a3a;
        border-radius:10px;
        padding:18px 12px;
        text-align:center;
        border:1px solid #2d4a6e;
    }
    .kpi-box .kpi-value { font-size:1.8rem; font-weight:700; color:#4fc3f7; }
    .kpi-box .kpi-label { font-size:0.8rem; color:#90a4ae; margin-top:4px; }

    .sidebar-badge {
        background:#0f3460;
        border-radius:8px;
        padding:8px 12px;
        margin:6px 0;
        font-size:0.8rem;
        color:#a8b2d8;
    }
    [data-testid="stMetricValue"] { color: #4fc3f7 !important; }
    .stDataFrame thead { background:#0f3460; }
</style>
""", unsafe_allow_html=True)


# ============================================================
# PROLOG RULES SIMULATOR  (pure Python — always works)
# ============================================================

def simulate_prolog_decision(user_data: dict):
    risk      = float(user_data.get("risk_score", 0.5))
    compliance= str(user_data.get("compliance_status", "")).lower()
    failed    = int(user_data.get("failed_login_attempts", 0))
    sensitivity = str(user_data.get("access_sensitivity_level", "")).lower()
    time_of_day = str(user_data.get("time_of_day", "")).lower()
    role      = str(user_data.get("role", "")).lower()
    dept      = str(user_data.get("department", "")).lower()

    # 🔴 DENY
    if risk >= 0.66 and "non" in compliance:
        return "deny", "High risk + non-compliant status"
    if failed >= 3 and sensitivity == "high":
        return "deny", f"Excessive failed logins ({failed}) with high-sensitivity access"
    if "non" in compliance and sensitivity == "high":
        return "deny", "Non-compliant user attempting high-sensitivity access"

    # 🟡 REVIEW
    if time_of_day in ["night", "evening"] and sensitivity == "high":
        return "review", "Off-hours access to high-sensitivity resource — manual review required"
    if failed == 2 and 0.33 <= risk < 0.66:
        return "review", "Borderline risk with recent failed attempts"

    # 🟢 APPROVE
    if "compliant" in compliance and risk < 0.4 and failed < 3 and sensitivity == "low":
        return "approve", "Low-risk compliant user accessing low-sensitivity resource"
    if role == "admin" and "compliant" in compliance and risk < 0.5:
        return "approve", "Admin with good compliance and risk profile"
    if role == "manager" and dept == "engineering" and "compliant" in compliance and risk < 0.4 and failed == 0:
        return "approve", "Engineering manager with perfect compliance"

    return "review", "Manual review recommended — does not fully satisfy approve/deny criteria"


# ============================================================
# PROLOG INTEGRATION  (optional — silent fallback)
# ============================================================

@st.cache_resource(show_spinner=False)
def _try_init_prolog():
    try:
        from pyswip import Prolog
        import os
        p = Prolog()
        for f in ("rules.pl", "facts.pl"):
            if os.path.exists(f):
                p.consult(f)
        return p, True
    except Exception:
        return None, False

prolog, prolog_available = _try_init_prolog()


def query_prolog_or_sim(uid, facts, fallback_dict):
    if prolog_available and prolog is not None:
        try:
            for fact in facts:
                prolog.assertz(fact)
            decision, reason = "review", "No rule matched"
            d = list(prolog.query(f"access_decision({uid}, D)"))
            if d:
                decision = str(d[0]["D"])
            for rule_fn in [f"why_denied({uid}, R)", f"why_review({uid}, R)", f"why_approved({uid}, R)"]:
                r = list(prolog.query(rule_fn))
                if r:
                    reason = str(r[0]["R"])
                    break
            for fact in facts:
                pred = fact.split("(")[0]
                try:
                    prolog.retractall(f"{pred}({uid}, _)")
                except Exception:
                    pass
            return decision, reason
        except Exception:
            pass
    return simulate_prolog_decision(fallback_dict)


# ============================================================
# DATA  — load CSV or generate realistic synthetic data
# ============================================================

ROLES        = ["admin", "manager", "developer", "analyst", "guest", "engineer", "hr_officer"]
DEPARTMENTS  = ["engineering", "finance", "hr", "it", "security", "operations", "legal"]
COMPLIANCE   = ["Compliant", "Non-Compliant"]
SENSITIVITY  = ["Low", "Medium", "High"]
TIMES        = ["Morning", "Afternoon", "Evening", "Night"]

@st.cache_data(show_spinner=False)
def load_data():
    import os
    csv_path = "access_control_authentic_500k.csv"
    if os.path.exists(csv_path):
        try:
            df = pd.read_csv(csv_path, low_memory=False)
            if "login_time" in df.columns:
                df["login_time"] = pd.to_datetime(df["login_time"], errors="coerce")
            return df, False   # (data, is_synthetic)
        except Exception:
            pass

    # ── Synthetic data fallback ──────────────────────────────
    rng = np.random.default_rng(42)
    n = 500
    risk_scores = rng.beta(2, 5, n).round(4)
    failed      = rng.integers(0, 8, n)
    compliances = rng.choice(COMPLIANCE, n, p=[0.65, 0.35])
    sensitivities = rng.choice(SENSITIVITY, n)
    times       = rng.choice(TIMES, n)
    roles       = rng.choice(ROLES, n)
    departments = rng.choice(DEPARTMENTS, n)
    anomaly     = (risk_scores > 0.6).astype(int)
    access_granted = ((risk_scores < 0.5) & (compliances == "Compliant")).astype(int)
    prev_access = rng.integers(0, 50, n)

    df = pd.DataFrame({
        "user_id":                [f"U{str(i).zfill(5)}" for i in range(n)],
        "role":                   roles,
        "department":             departments,
        "risk_score":             risk_scores,
        "compliance_status":      compliances,
        "failed_login_attempts":  failed,
        "access_sensitivity_level": sensitivities,
        "time_of_day":            times,
        "anomaly_detected":       anomaly,
        "access_granted":         access_granted,
        "previous_access_count":  prev_access,
    })
    return df, True   # (data, is_synthetic)


# ============================================================
# HELPERS
# ============================================================

def to_atom(x):
    if pd.isna(x):
        return "unknown"
    return str(x).strip().lower().replace(" ", "_").replace("-", "_").replace("/", "_")

def risk_label(score):
    try:
        s = float(score)
    except Exception:
        return "unknown"
    return "high" if s >= 0.66 else ("medium" if s >= 0.33 else "low")

def compliance_label(x):
    s = to_atom(x)
    return "non_compliant" if "non" in s else ("compliant" if "compliant" in s else s)

def make_facts(row):
    uid = to_atom(row["user_id"])
    facts = [
        f"role({uid}, {to_atom(row['role'])})",
        f"department({uid}, {to_atom(row['department'])})",
        f"risk_score({uid}, {risk_label(row['risk_score'])})",
        f"compliance({uid}, {compliance_label(row['compliance_status'])})",
        f"failed_logins({uid}, {int(row['failed_login_attempts'])})",
        f"sensitivity({uid}, {to_atom(row['access_sensitivity_level'])})",
        f"time_of_day({uid}, {to_atom(row['time_of_day'])})",
    ]
    return uid, facts

def decision_badge(decision):
    d = decision.lower()
    if d == "approve":
        return '<span style="background:#1a472a;color:#2ecc71;padding:4px 12px;border-radius:20px;font-weight:700;">✅ APPROVE</span>'
    if d == "deny":
        return '<span style="background:#4a1122;color:#e74c3c;padding:4px 12px;border-radius:20px;font-weight:700;">❌ DENY</span>'
    return '<span style="background:#4a3800;color:#f39c12;padding:4px 12px;border-radius:20px;font-weight:700;">⚠️ REVIEW</span>'


# ============================================================
# LOAD DATA  (cached — no spinner shown on sidebar/nav)
# ============================================================

df, is_synthetic = load_data()


# ============================================================
# SIDEBAR
# ============================================================

with st.sidebar:
    st.markdown("""
    <div style='text-align:center;padding:10px 0 5px;'>
        <span style='font-size:2.5rem;'>🔐</span>
        <h3 style='color:#e94560;margin:4px 0 0;'>AccessGuard</h3>
        <p style='color:#90a4ae;font-size:0.78rem;margin:0;'>Smart Policy Optimizer</p>
    </div>
    """, unsafe_allow_html=True)
    st.divider()

    page = st.radio(
        "Navigation",
        ["🏠 Dashboard Overview",
         "🔍 Single User Lookup",
         "📊 Risk Analytics",
         "⚙️ Policy Simulator",
         "🧠 A* Graph Search",
         "🧬 Genetic Algorithm",
         "⚡ Hybrid AI Analysis"],
        label_visibility="collapsed"
    )

    st.divider()

    engine_status = "✅ Prolog Active" if prolog_available else "🐍 Python Simulation"
    data_status   = "⚠️ Synthetic Data" if is_synthetic else "✅ Real Dataset"

    st.markdown(f"""
    <div class='sidebar-badge'>🧠 Engine: {engine_status}</div>
    <div class='sidebar-badge'>📂 Data: {data_status}</div>
    <div class='sidebar-badge'>📅 Records: {len(df):,}</div>
    """, unsafe_allow_html=True)

    if is_synthetic:
        st.info("💡 Place `access_control.csv` in the project folder to use real data.")

    st.divider()
    st.caption("Smart Access Control v2.0\nAI Lab Project · Spring 2026")


# ============================================================
# HEADER  (shown on every page)
# ============================================================

st.markdown("""
<div class='main-header'>
    <h1>🔐 Smart Passwordless Access Control Policy Optimizer</h1>
    <p>AI-powered Identity & Access Management · Machine Learning · Prolog Rule Engine · Genetic Algorithm</p>
</div>
""", unsafe_allow_html=True)


# ============================================================
# PAGE: DASHBOARD OVERVIEW
# ============================================================

if page == "🏠 Dashboard Overview":

    # ── KPIs ────────────────────────────────────────────────
    c1, c2, c3, c4, c5 = st.columns(5)
    kpis = [
        ("Total Records",     f"{len(df):,}",                          "📋"),
        ("Unique Users",      f"{df['user_id'].nunique():,}",           "👤"),
        ("Grant Rate",        f"{df['access_granted'].mean()*100:.1f}%","✅"),
        ("Anomaly Rate",      f"{df['anomaly_detected'].mean()*100:.1f}%","⚠️"),
        ("Avg Risk Score",    f"{df['risk_score'].mean():.3f}",         "📈"),
    ]
    for col, (label, value, icon) in zip([c1,c2,c3,c4,c5], kpis):
        with col:
            st.markdown(f"""
            <div class='kpi-box'>
                <div style='font-size:1.4rem;'>{icon}</div>
                <div class='kpi-value'>{value}</div>
                <div class='kpi-label'>{label}</div>
            </div>""", unsafe_allow_html=True)

    st.markdown("<br>", unsafe_allow_html=True)

    # ── Charts row 1 ─────────────────────────────────────────
    col1, col2 = st.columns(2)

    with col1:
        st.subheader("📊 Access Grant Rate by Compliance")
        comp_rate = df.groupby("compliance_status")["access_granted"].mean()
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a")
        ax.set_facecolor("#1e2a3a")
        colors = ["#2ecc71", "#e74c3c"]
        bars = ax.bar(comp_rate.index, comp_rate.values, color=colors, width=0.5, edgecolor="none")
        for bar, val in zip(bars, comp_rate.values):
            ax.text(bar.get_x() + bar.get_width()/2, bar.get_height()+0.01, f"{val:.1%}",
                    ha="center", va="bottom", color="white", fontweight="bold")
        ax.set_ylabel("Grant Rate", color="#a8b2d8")
        ax.set_title("Access Granted by Compliance Status", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8")
        for spine in ax.spines.values(): spine.set_visible(False)
        ax.yaxis.grid(True, color="#2d4a6e", alpha=0.5)
        ax.set_axisbelow(True)
        st.pyplot(fig); plt.close()

    with col2:
        st.subheader("🌙 Anomaly Rate by Time of Day")
        time_anomaly = df.groupby("time_of_day")["anomaly_detected"].mean().sort_values(ascending=False)
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        cmap_colors = ["#e94560", "#f39c12", "#4ecdc4", "#45b7d1"]
        ax.bar(time_anomaly.index, time_anomaly.values,
               color=cmap_colors[:len(time_anomaly)], width=0.5, edgecolor="none")
        ax.set_ylabel("Anomaly Rate", color="#a8b2d8")
        ax.set_title("Anomaly Detection by Time of Day", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8")
        for spine in ax.spines.values(): spine.set_visible(False)
        ax.yaxis.grid(True, color="#2d4a6e", alpha=0.5)
        ax.set_axisbelow(True)
        st.pyplot(fig); plt.close()

    # ── Charts row 2 ─────────────────────────────────────────
    col1, col2 = st.columns(2)

    with col1:
        st.subheader("👔 Grant Rate by Role (Top 7)")
        role_acc = df.groupby("role")["access_granted"].mean().sort_values(ascending=False).head(7)
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        palette = plt.cm.viridis(np.linspace(0.3, 0.9, len(role_acc)))
        ax.barh(role_acc.index, role_acc.values, color=palette, edgecolor="none")
        ax.set_xlabel("Grant Rate", color="#a8b2d8")
        ax.set_title("Access Granted by Role", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8"); ax.invert_yaxis()
        for spine in ax.spines.values(): spine.set_visible(False)
        ax.xaxis.grid(True, color="#2d4a6e", alpha=0.5); ax.set_axisbelow(True)
        st.pyplot(fig); plt.close()

    with col2:
        st.subheader("🏢 Grant Rate by Department (Top 7)")
        dept_acc = df.groupby("department")["access_granted"].mean().sort_values(ascending=False).head(7)
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        palette = plt.cm.plasma(np.linspace(0.2, 0.85, len(dept_acc)))
        ax.barh(dept_acc.index, dept_acc.values, color=palette, edgecolor="none")
        ax.set_xlabel("Grant Rate", color="#a8b2d8")
        ax.set_title("Access Granted by Department", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8"); ax.invert_yaxis()
        for spine in ax.spines.values(): spine.set_visible(False)
        ax.xaxis.grid(True, color="#2d4a6e", alpha=0.5); ax.set_axisbelow(True)
        st.pyplot(fig); plt.close()

    # ── Recent Activity Table ────────────────────────────────
    st.subheader("📋 Live Access Decisions — Sample Records")
    sample = df.sample(min(15, len(df)), random_state=1).copy()
    decisions, reasons = [], []
    for _, row in sample.iterrows():
        dec, rea = simulate_prolog_decision(row.to_dict())
        decisions.append(dec.upper()); reasons.append(rea[:60] + ("…" if len(rea) > 60 else ""))
    sample["decision"] = decisions; sample["reason"] = reasons

    display_cols = ["user_id", "role", "department", "risk_score",
                    "compliance_status", "decision", "reason"]
    def color_dec(val):
        c = {"APPROVE": "#1a472a", "DENY": "#4a1122", "REVIEW": "#4a3800"}.get(val, "")
        t = {"APPROVE": "#2ecc71", "DENY": "#e74c3c", "REVIEW": "#f39c12"}.get(val, "white")
        return f"background-color:{c};color:{t};font-weight:bold;" if c else ""

    styled = sample[display_cols].style.map(color_dec, subset=["decision"])
    st.dataframe(styled, use_container_width=True, hide_index=True)

    # ── Prolog Rules Expander ────────────────────────────────
    with st.expander("📜 Active Access Control Rules (Prolog)", expanded=False):
        st.code("""
% 🔴 DENY ACCESS
deny_access(User) :- risk_score(User, high), compliance(User, non_compliant).
deny_access(User) :- failed_logins(User, N), N >= 3, sensitivity(User, high).
deny_access(User) :- compliance(User, non_compliant), sensitivity(User, high).

% 🟡 REVIEW ACCESS
review_access(User) :- time_of_day(User, night), sensitivity(User, high).
review_access(User) :- failed_logins(User, 2), risk_score(User, medium).

% 🟢 APPROVE ACCESS
approve_access(User) :-
    compliance(User, compliant), risk_score(User, low),
    failed_logins(User, N), N < 3, sensitivity(User, low).
approve_access(User) :-
    role(User, admin), compliance(User, compliant), risk_score(User, low).
approve_access(User) :-
    role(User, manager), department(User, engineering),
    compliance(User, compliant), risk_score(User, low), failed_logins(User, 0).
        """, language="prolog")


# ============================================================
# PAGE: SINGLE USER LOOKUP
# ============================================================

elif page == "🔍 Single User Lookup":
    st.subheader("🔍 Evaluate Access for a Specific User")

    col1, col2 = st.columns([1, 2], gap="large")

    with col1:
        st.markdown("**Enter User ID manually or pick an example:**")
        example_ids = df["user_id"].astype(str).head(30).tolist()
        chosen = st.selectbox("📌 Example Users", ["— select —"] + example_ids)
        user_input = st.text_input("Or type User ID:", value=(chosen if chosen != "— select —" else ""))
        go = st.button("🎯 Evaluate Access", type="primary", use_container_width=True)

    with col2:
        if go and user_input:
            matches = df[df["user_id"].astype(str) == user_input.strip()]
            if matches.empty:
                st.error(f"❌ User `{user_input}` not found.")
            else:
                row = matches.iloc[0]
                uid, facts = make_facts(row)
                decision, reason = query_prolog_or_sim(uid, facts, row.to_dict())

                card_class = {"approve": "card-approve", "deny": "card-deny"}.get(decision, "card-review")
                icon = {"approve": "✅ ACCESS GRANTED", "deny": "❌ ACCESS DENIED"}.get(decision, "⚠️ MANUAL REVIEW")
                st.markdown(f'<div class="decision-card {card_class}">{icon}</div>', unsafe_allow_html=True)

                # Metrics
                m1, m2, m3 = st.columns(3)
                m1.metric("Role", row["role"]); m1.metric("Department", row["department"])
                m2.metric("Risk Score", f"{row['risk_score']:.3f}"); m2.metric("Compliance", row["compliance_status"])
                m3.metric("Failed Logins", int(row["failed_login_attempts"])); m3.metric("Sensitivity", row["access_sensitivity_level"])

                st.info(f"**🧠 Rule Engine Explanation:** {reason}")

                with st.expander("📜 Generated Prolog Facts"):
                    st.code("\n".join(f + "." for f in facts), language="prolog")
        elif go:
            st.warning("Please enter or select a User ID first.")
        else:
            st.markdown("""
            <div style='text-align:center;padding:60px 20px;color:#90a4ae;'>
                <div style='font-size:3rem;'>🔍</div>
                <p>Select a user and click <strong>Evaluate Access</strong> to see the decision</p>
            </div>""", unsafe_allow_html=True)


# ============================================================
# PAGE: RISK ANALYTICS
# ============================================================

elif page == "📊 Risk Analytics":
    st.subheader("📊 Access Risk Analytics Dashboard")

    c1, c2, c3, c4 = st.columns(4)
    metrics = [
        ("Total Users",    f"{df['user_id'].nunique():,}"),
        ("Grant Rate",     f"{df['access_granted'].mean()*100:.1f}%"),
        ("Anomaly Rate",   f"{df['anomaly_detected'].mean()*100:.1f}%"),
        ("Avg Risk Score", f"{df['risk_score'].mean():.3f}"),
    ]
    for col, (label, val) in zip([c1,c2,c3,c4], metrics):
        col.metric(label, val)

    st.divider()

    # Risk distribution
    col1, col2 = st.columns(2)
    with col1:
        st.subheader("📈 Risk Score Distribution")
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        ax.hist(df["risk_score"], bins=40, color="#e94560", edgecolor="none", alpha=0.85)
        ax.axvline(df["risk_score"].mean(),   color="#4fc3f7", lw=2, linestyle="--", label=f'Mean {df["risk_score"].mean():.2f}')
        ax.axvline(df["risk_score"].median(), color="#2ecc71", lw=2, linestyle=":",  label=f'Median {df["risk_score"].median():.2f}')
        ax.legend(facecolor="#1e2a3a", labelcolor="white")
        ax.set_xlabel("Risk Score", color="#a8b2d8"); ax.set_ylabel("Frequency", color="#a8b2d8")
        ax.set_title("Risk Score Distribution", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8")
        for spine in ax.spines.values(): spine.set_visible(False)
        st.pyplot(fig); plt.close()

    with col2:
        st.subheader("🛡️ Risk Score by Compliance")
        fig, ax = plt.subplots(figsize=(6, 4))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        groups = [df[df["compliance_status"]==c]["risk_score"].dropna() for c in df["compliance_status"].unique()]
        bp = ax.boxplot(groups, labels=df["compliance_status"].unique(),
                        patch_artist=True, widths=0.4,
                        boxprops=dict(facecolor="#0f3460", color="#4fc3f7"),
                        medianprops=dict(color="#e94560", linewidth=2),
                        whiskerprops=dict(color="#a8b2d8"),
                        capprops=dict(color="#a8b2d8"),
                        flierprops=dict(marker="o", color="#f39c12", alpha=0.4, markersize=3))
        ax.set_xlabel("Compliance Status", color="#a8b2d8")
        ax.set_ylabel("Risk Score", color="#a8b2d8")
        ax.set_title("Risk by Compliance Status", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8")
        for spine in ax.spines.values(): spine.set_visible(False)
        ax.yaxis.grid(True, color="#2d4a6e", alpha=0.5); ax.set_axisbelow(True)
        st.pyplot(fig); plt.close()

    # Correlation heatmap
    st.subheader("📈 Feature Correlation Heatmap")
    numeric_cols = [c for c in ["risk_score", "anomaly_detected", "failed_login_attempts",
                                 "previous_access_count", "access_granted"] if c in df.columns]
    if len(numeric_cols) >= 2:
        corr = df[numeric_cols].corr()
        fig, ax = plt.subplots(figsize=(8, 5))
        fig.patch.set_facecolor("#1e2a3a"); ax.set_facecolor("#1e2a3a")
        sns.heatmap(corr, annot=True, fmt=".2f", cmap="coolwarm", center=0,
                    ax=ax, linewidths=0.5, linecolor="#1e2a3a",
                    annot_kws={"color": "white"})
        ax.set_title("Correlation Between Key Metrics", color="white", pad=12)
        ax.tick_params(colors="#a8b2d8")
        st.pyplot(fig); plt.close()


# ============================================================
# PAGE: POLICY SIMULATOR
# ============================================================

elif page == "⚙️ Policy Simulator":
    st.subheader("⚙️ What-If Policy Simulator")
    st.markdown("Adjust user attributes and instantly see what the rule engine decides.")

    col1, col2 = st.columns([1, 1], gap="large")

    with col1:
        st.markdown("**👤 User Attributes**")
        sim_role       = st.selectbox("Role", ROLES)
        sim_dept       = st.selectbox("Department", DEPARTMENTS)
        sim_risk       = st.slider("Risk Score", 0.0, 1.0, 0.3, 0.01,
                                   help="0 = safe, 1 = very risky")
        sim_compliance = st.selectbox("Compliance Status", COMPLIANCE)
        sim_failed     = st.number_input("Failed Login Attempts", 0, 10, 0)
        sim_sensitivity= st.selectbox("Access Sensitivity Level", SENSITIVITY)
        sim_time       = st.selectbox("Time of Day", TIMES)

        run = st.button("🚀 Run Simulation", type="primary", use_container_width=True)

    with col2:
        if run:
            sim_data = {
                "user_id":                  "sim_user",
                "role":                     sim_role,
                "department":               sim_dept,
                "risk_score":               sim_risk,
                "compliance_status":        sim_compliance,
                "failed_login_attempts":    sim_failed,
                "access_sensitivity_level": sim_sensitivity,
                "time_of_day":              sim_time,
            }
            decision, reason = simulate_prolog_decision(sim_data)

            card_class = {"approve":"card-approve","deny":"card-deny"}.get(decision,"card-review")
            icon = {"approve":"✅ ACCESS GRANTED","deny":"❌ ACCESS DENIED"}.get(decision,"⚠️ MANUAL REVIEW REQUIRED")
            st.markdown(f'<div class="decision-card {card_class}" style="font-size:1.2rem;padding:25px;">{icon}</div>',
                        unsafe_allow_html=True)
            st.markdown(f"<br>**🧠 Reason:** {reason}", unsafe_allow_html=True)

            # Risk gauge
            st.markdown("**Risk Level Gauge:**")
            risk_color = "#e74c3c" if sim_risk >= 0.66 else ("#f39c12" if sim_risk >= 0.33 else "#2ecc71")
            st.markdown(f"""
            <div style='background:#2d4a6e;border-radius:8px;height:18px;overflow:hidden;'>
                <div style='background:{risk_color};width:{sim_risk*100:.0f}%;height:100%;border-radius:8px;
                            transition:width 0.5s;'></div>
            </div>
            <p style='color:#a8b2d8;font-size:0.8rem;margin-top:4px;'>Risk: {sim_risk:.2f} 
               ({'High' if sim_risk>=0.66 else 'Medium' if sim_risk>=0.33 else 'Low'})</p>
            """, unsafe_allow_html=True)

            # Matching rule snippet
            st.markdown("**📜 Matching Prolog Rule:**")
            if decision == "approve":
                rule = """approve_access(User) :-
    compliance(User, compliant),
    risk_score(User, low),
    failed_logins(User, N), N < 3,
    sensitivity(User, low)."""
            elif decision == "deny":
                if sim_failed >= 3:
                    rule = """deny_access(User) :-
    failed_logins(User, N), N >= 3,
    sensitivity(User, high)."""
                else:
                    rule = """deny_access(User) :-
    risk_score(User, high),
    compliance(User, non_compliant)."""
            else:
                rule = """review_access(User) :-
    time_of_day(User, night),
    sensitivity(User, high).
% fallback: does not satisfy approve or deny criteria"""
            st.code(rule, language="prolog")

            if decision == "approve":
                st.balloons()
        else:
            st.markdown("""
            <div style='text-align:center;padding:80px 20px;color:#90a4ae;'>
                <div style='font-size:3rem;'>⚙️</div>
                <p>Configure attributes on the left and click <strong>Run Simulation</strong></p>
            </div>""", unsafe_allow_html=True)


# ============================================================
# PAGE: A* GRAPH SEARCH
# ============================================================

elif page == "🧠 A* Graph Search":
    st.subheader("🧠 A* Graph-Based Access Decision Analysis")
    st.markdown("""
    The **A* search algorithm** finds the optimal path through an access decision graph.
    It models access control as a graph where nodes represent states (role, risk, compliance)
    and edges are valid transitions.
    
    **How it works:**
    - Builds an access decision graph based on RBAC policies
    - Uses A* search to find the lowest-cost path to approval
    - Heuristic: distance to compliant, low-risk state
    - Returns optimal access path with cost analysis
    """)
    
    if not astar_available:
        st.error("⚠️ A* module not available. Please ensure `astar_decision_engine.py` is in the project folder.")
    else:
        col1, col2 = st.columns([1, 1], gap="large")
        
        with col1:
            st.markdown("**Configure Access Request**")
            astar_role = st.selectbox("Role", ROLES, key="astar_role")
            astar_dept = st.selectbox("Department", DEPARTMENTS, key="astar_dept")
            astar_risk = st.slider("Risk Score", 0.0, 1.0, 0.5, 0.01, key="astar_risk")
            astar_compliance = st.selectbox("Compliance Status", COMPLIANCE, key="astar_compliance")
            astar_failed = st.number_input("Failed Logins", 0, 10, 1, key="astar_failed")
            astar_sensitivity = st.selectbox("Sensitivity Level", SENSITIVITY, key="astar_sens")
            astar_time = st.selectbox("Time of Day", TIMES, key="astar_time")
            
            astar_run = st.button("🔍 Analyze with A*", type="primary", use_container_width=True, key="astar_btn")
        
        with col2:
            if astar_run:
                astar_data = {
                    "role": astar_role,
                    "department": astar_dept,
                    "risk_score": astar_risk,
                    "compliance_status": astar_compliance,
                    "failed_login_attempts": astar_failed,
                    "access_sensitivity_level": astar_sensitivity,
                    "time_of_day": astar_time,
                }
                
                with st.spinner("🔄 Running A* graph search..."):
                    astar_result = analyze_access_with_astar(astar_data)
                
                # Decision card
                decision = astar_result.get("decision", "review")
                card_class = {"approve": "card-approve", "deny": "card-deny"}.get(decision, "card-review")
                icon = {"approve": "✅ A* DECISION: APPROVE", "deny": "❌ A* DECISION: DENY"}.get(decision, "⚠️ A* DECISION: REVIEW")
                st.markdown(f'<div class="decision-card {card_class}">{icon}</div>', unsafe_allow_html=True)
                
                # Metrics
                m1, m2, m3 = st.columns(3)
                m1.metric("Path Length", astar_result.get("path_length", 0), "steps")
                m2.metric("Total Cost", f"{astar_result.get('total_cost', 0):.3f}", "units")
                m3.metric("Algorithm", "A* Search")
                
                # Path details
                st.markdown("**📍 Optimal Access Path:**")
                st.info(astar_result.get("path_description", "No path found"))
                
                # Path steps table
                if astar_result.get("path_steps"):
                    st.markdown("**Path Steps Breakdown:**")
                    steps_df = pd.DataFrame(astar_result["path_steps"])
                    st.dataframe(steps_df, use_container_width=True, hide_index=True)
                
                st.success(f"✅ {astar_result.get('recommendation', '')}")
            else:
                st.markdown("""
                <div style='text-align:center;padding:80px 20px;color:#90a4ae;'>
                    <div style='font-size:3rem;'>🧠</div>
                    <p>Configure an access request and click <strong>Analyze with A*</strong></p>
                </div>""", unsafe_allow_html=True)


# ============================================================
# PAGE: GENETIC ALGORITHM
# ============================================================

elif page == "🧬 Genetic Algorithm":
    st.subheader("🧬 Genetic Algorithm for Policy Optimization")
    st.markdown("""
    The **Genetic Algorithm** evolves access control policies across generations
    to optimize the balance between security, usability, and compliance.
    
    **How it works:**
    - Population: Multiple policy rule sets (chromosomes)
    - Fitness: Security score + Usability score - Compliance violations
    - Crossover: Combine parent policies to create children
    - Mutation: Random modifications to policy parameters
    - Selection: Tournament selection keeps best policies
    """)
    
    if not ga_available:
        st.error("⚠️ Genetic Algorithm module not available. Please ensure `genetic_policy_optimizer.py` is in the project folder.")
    else:
        st.markdown("**Optimization Parameters:**")
        col1, col2, col3 = st.columns(3)
        
        with col1:
            ga_pop_size = st.slider("Population Size", 10, 50, 20, key="ga_pop")
        with col2:
            ga_generations = st.slider("Generations", 10, 100, 50, key="ga_gen")
        with col3:
            ga_sample = st.slider("Training Sample Size", 100, 1000, 500, key="ga_sample")
        
        ga_run = st.button("🧬 Run GA Optimization", type="primary", use_container_width=True, key="ga_btn")
        
        if ga_run:
            st.info("⏳ GA Evolution in Progress... (this may take 5-30 seconds)")
            
            with st.spinner(f"🔄 Evolving {ga_pop_size} policies over {ga_generations} generations..."):
                df = load_data()[0]
                
                # Prepare training data
                sampled_df = df.sample(min(ga_sample, len(df)), random_state=42)
                training_data = sampled_df.to_dict('records')
                
                # Run GA
                ga_result = optimize_policies(training_data, ga_pop_size, ga_generations)
            
            # Display results
            st.markdown("**🏆 Optimization Results:**")
            
            col1, col2, col3 = st.columns(3)
            col1.metric("Generations", ga_result["generations_run"])
            col2.metric("Best Fitness", f"{ga_result['best_policies'][0]['fitness_score']:.4f}" if ga_result['best_policies'] else "N/A")
            col3.metric("Improvement", f"{ga_result.get('improvement', 0):.2f}%")
            
            # Fitness history chart
            st.markdown("**📊 Fitness Evolution Over Generations:**")
            fig, ax = plt.subplots(figsize=(10, 4))
            fig.patch.set_facecolor("#1e2a3a")
            ax.set_facecolor("#1e2a3a")
            ax.plot(ga_result["best_fitness_history"], color="#4fc3f7", linewidth=2.5, marker="o", markersize=4)
            ax.set_xlabel("Generation", color="#a8b2d8")
            ax.set_ylabel("Best Fitness Score", color="#a8b2d8")
            ax.set_title("GA Fitness Convergence", color="white", fontweight="bold")
            ax.tick_params(colors="#a8b2d8")
            ax.grid(True, color="#2d4a6e", alpha=0.3)
            st.pyplot(fig)
            plt.close()
            
            # Best policies
            st.markdown("**🥇 Top Evolved Policies:**")
            for rank, policy in enumerate(ga_result["best_policies"], 1):
                with st.expander(f"Policy #{rank} - Fitness: {policy['fitness_score']:.4f} ({policy['num_rules']} rules)"):
                    st.write(f"**Rank:** {rank}")
                    st.write(f"**Fitness Score:** {policy['fitness_score']:.4f}")
                    st.write(f"**Number of Rules:** {policy['num_rules']}")
                    
                    st.markdown("**Rules:**")
                    for i, rule in enumerate(policy['rules'], 1):
                        st.write(f"  {i}. {rule['name']} (priority: {rule['priority']})")
                        st.write(f"     - Condition: {rule['condition']}")
                        st.write(f"     - Action: {rule['action']}")
                        st.write(f"     - Weight: {rule['weight']}")
            
            st.success(f"✅ {ga_result['recommendation']}")


# ============================================================
# PAGE: HYBRID AI ANALYSIS
# ============================================================

elif page == "⚡ Hybrid AI Analysis":
    st.subheader("⚡ Hybrid AI Analysis (Prolog + A* + GA)")
    st.markdown("""
    **Hybrid Analysis** combines three AI techniques for comprehensive security assessment:
    - **Prolog:** Rule-based symbolic reasoning
    - **A* Search:** Graph-based optimal path analysis
    - **Genetic Algorithm:** Policy optimization
    
    The system reaches a **consensus decision** by comparing all three algorithms.
    """)
    
    if not (astar_available and ga_available):
        st.warning("⚠️ Some modules not available. Hybrid analysis may be limited.")
    
    col1, col2 = st.columns([1, 1], gap="large")
    
    with col1:
        st.markdown("**Access Request Details**")
        hybrid_role = st.selectbox("Role", ROLES, key="hybrid_role")
        hybrid_dept = st.selectbox("Department", DEPARTMENTS, key="hybrid_dept")
        hybrid_risk = st.slider("Risk Score", 0.0, 1.0, 0.45, 0.01, key="hybrid_risk")
        hybrid_compliance = st.selectbox("Compliance Status", COMPLIANCE, key="hybrid_compliance")
        hybrid_failed = st.number_input("Failed Logins", 0, 10, 0, key="hybrid_failed")
        hybrid_sensitivity = st.selectbox("Sensitivity Level", SENSITIVITY, key="hybrid_sens")
        hybrid_time = st.selectbox("Time of Day", TIMES, key="hybrid_time")
        
        hybrid_run = st.button("⚡ Run Hybrid Analysis", type="primary", use_container_width=True, key="hybrid_btn")
    
    with col2:
        if hybrid_run:
            hybrid_data = {
                "user_id": "hybrid_user",
                "role": hybrid_role,
                "department": hybrid_dept,
                "risk_score": hybrid_risk,
                "compliance_status": hybrid_compliance,
                "failed_login_attempts": hybrid_failed,
                "access_sensitivity_level": hybrid_sensitivity,
                "time_of_day": hybrid_time,
            }
            
            with st.spinner("🔄 Running Prolog, A*, and GA analysis..."):
                # 1. Prolog
                prolog_dec, prolog_reason = simulate_prolog_decision(hybrid_data)
                
                # 2. A*
                astar_result = analyze_access_with_astar(hybrid_data) if astar_available else None
                
                # 3. GA (quick evaluation)
                ga_result = None
                if ga_available:
                    df = load_data()[0]
                    training_data = df.sample(min(300, len(df)), random_state=42).to_dict('records')
                    ga_result = optimize_policies(training_data, 10, 20)
            
            # Display results
            st.markdown("### 🎯 Algorithm Comparison")
            
            comparison_data = {
                "Algorithm": ["Prolog", "A* Search", "Genetic Algorithm"],
                "Decision": [
                    prolog_dec.upper(),
                    astar_result["decision"].upper() if astar_result else "N/A",
                    "OPTIMIZED" if ga_result else "N/A"
                ],
                "Confidence": ["HIGH", "MEDIUM", "MEDIUM"]
            }
            st.dataframe(pd.DataFrame(comparison_data), use_container_width=True, hide_index=True)
            
            # Consensus
            consensus = prolog_dec
            if astar_result and astar_result["decision"] == prolog_dec:
                consensus_confidence = "VERY HIGH"
                agreement = "✅ All algorithms agree!"
            else:
                consensus_confidence = "MEDIUM"
                agreement = "⚠️ Algorithms have different recommendations"
            
            st.markdown("### 🏆 Consensus Decision")
            decision_card_class = {"approve": "card-approve", "deny": "card-deny"}.get(consensus, "card-review")
            decision_icon = {"approve": "✅ CONSENSUS: APPROVE", "deny": "❌ CONSENSUS: DENY"}.get(consensus, "⚠️ CONSENSUS: REVIEW")
            st.markdown(f'<div class="decision-card {decision_card_class}" style="font-size:1.3rem;padding:30px;">{decision_icon}</div>', 
                       unsafe_allow_html=True)
            
            col1, col2, col3 = st.columns(3)
            col1.metric("Consensus", consensus.upper())
            col2.metric("Confidence", consensus_confidence)
            col3.metric("Unanimous", "Yes" if agreement.startswith("✅") else "No")
            
            st.info(agreement)
            
            # Detailed breakdown
            st.markdown("### 📋 Detailed Analysis")
            
            st.markdown("**1️⃣ Prolog Rule Engine:**")
            st.write(f"   Decision: **{prolog_dec.upper()}**")
            st.write(f"   Reason: {prolog_reason}")
            
            if astar_result:
                st.markdown("**2️⃣ A* Graph Search:**")
                st.write(f"   Decision: **{astar_result['decision'].upper()}**")
                st.write(f"   Path Steps: {astar_result.get('path_length', 0)}")
                st.write(f"   Total Cost: {astar_result.get('total_cost', 0):.3f}")
            
            if ga_result:
                st.markdown("**3️⃣ Genetic Algorithm:**")
                st.write(f"   Best Policy Fitness: {ga_result['best_policies'][0]['fitness_score']:.4f}")
                st.write(f"   Policies Evolved: {len(ga_result['best_policies'])}")
                st.write(f"   Improvement: {ga_result.get('improvement', 0):.2f}%")
            
            st.success("✅ Hybrid AI analysis complete. Use consensus decision for production systems.")
        else:
            st.markdown("""
            <div style='text-align:center;padding:80px 20px;color:#90a4ae;'>
                <div style='font-size:3rem;'>⚡</div>
                <p>Configure an access request and click <strong>Run Hybrid Analysis</strong></p>
            </div>""", unsafe_allow_html=True)


# ============================================================
# FOOTER
# ============================================================

st.divider()
st.markdown("""
<div style='text-align:center;color:#555e7b;font-size:0.78rem;padding:6px 0;'>
    🔐 Smart Access Control Policy Optimizer &nbsp;|&nbsp;
    AI Lab CSL-411 · Spring 2026 &nbsp;|&nbsp;
    Esha Ashfaq &amp; Hadiqa Mehmood &nbsp;|&nbsp;
    Powered by Prolog · A* · Genetic Algorithm · Streamlit · Python
</div>
""", unsafe_allow_html=True)
