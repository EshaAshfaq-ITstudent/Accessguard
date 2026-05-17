#!/usr/bin/env python3
"""Test script for API endpoints with A* and GA"""

import requests
import json
import time

BASE_URL = "http://localhost:8000"

def test_prolog_endpoint():
    """Test existing Prolog endpoint"""
    print("\n" + "="*60)
    print("Testing Prolog Endpoint")
    print("="*60)
    
    payload = {
        "role": "admin",
        "department": "engineering",
        "risk_score": 0.3,
        "compliance_status": "Compliant",
        "failed_login_attempts": 0,
        "access_sensitivity_level": "Low",
        "time_of_day": "Morning"
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/simulate", json=payload)
        if response.status_code == 200:
            print("✅ Prolog Endpoint Working")
            print(f"   Decision: {response.json()['decision']}")
        else:
            print(f"❌ Prolog Endpoint Error: {response.status_code}")
    except Exception as e:
        print(f"⚠️  Prolog Endpoint Unreachable: {str(e)}")

def test_astar_endpoint():
    """Test new A* endpoint"""
    print("\n" + "="*60)
    print("Testing A* Endpoint")
    print("="*60)
    
    payload = {
        "role": "manager",
        "department": "engineering",
        "risk_score": 0.5,
        "compliance_status": "Compliant",
        "failed_login_attempts": 1,
        "access_sensitivity_level": "Medium",
        "time_of_day": "Afternoon"
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/astar-analyze", json=payload)
        if response.status_code == 200:
            print("✅ A* Endpoint Working")
            data = response.json()
            print(f"   Decision: {data.get('decision')}")
            print(f"   Path Length: {data.get('path_length')}")
            print(f"   Algorithm: {data.get('algorithm')}")
        else:
            print(f"❌ A* Endpoint Error: {response.status_code}")
    except Exception as e:
        print(f"⚠️  A* Endpoint Unreachable: {str(e)}")

def test_astar_compare_endpoint():
    """Test A* vs Prolog comparison endpoint"""
    print("\n" + "="*60)
    print("Testing A* Comparison Endpoint")
    print("="*60)
    
    payload = {
        "role": "developer",
        "department": "finance",
        "risk_score": 0.6,
        "compliance_status": "Non-Compliant",
        "failed_login_attempts": 2,
        "access_sensitivity_level": "High",
        "time_of_day": "Night"
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/astar-compare", json=payload)
        if response.status_code == 200:
            print("✅ A* Comparison Endpoint Working")
            data = response.json()
            print(f"   Prolog Decision: {data['prolog_decision']['decision']}")
            print(f"   A* Decision: {data['astar_decision']['decision']}")
            print(f"   Agreement: {data['agreement']}")
        else:
            print(f"❌ A* Comparison Endpoint Error: {response.status_code}")
    except Exception as e:
        print(f"⚠️  A* Comparison Endpoint Unreachable: {str(e)}")

def test_ga_optimize_endpoint():
    """Test GA optimization endpoint"""
    print("\n" + "="*60)
    print("Testing GA Optimization Endpoint")
    print("="*60)
    
    try:
        response = requests.post(f"{BASE_URL}/api/ga-optimize-policies")
        if response.status_code == 200:
            print("✅ GA Optimization Endpoint Working")
            data = response.json()
            print(f"   Algorithm: {data.get('algorithm')}")
            print(f"   Generations: {data.get('generations_run')}")
            print(f"   Best Fitness: {data['best_policies'][0].get('fitness_score') if data.get('best_policies') else 'N/A'}")
        else:
            print(f"❌ GA Optimization Endpoint Error: {response.status_code}")
    except Exception as e:
        print(f"⚠️  GA Optimization Endpoint Unreachable: {str(e)}")

def test_hybrid_endpoint():
    """Test hybrid analysis endpoint"""
    print("\n" + "="*60)
    print("Testing Hybrid Analysis Endpoint")
    print("="*60)
    
    payload = {
        "role": "analyst",
        "department": "security",
        "risk_score": 0.45,
        "compliance_status": "Compliant",
        "failed_login_attempts": 1,
        "access_sensitivity_level": "High",
        "time_of_day": "Morning"
    }
    
    try:
        response = requests.post(f"{BASE_URL}/api/hybrid-analysis", json=payload)
        if response.status_code == 200:
            print("✅ Hybrid Analysis Endpoint Working")
            data = response.json()
            print(f"   Consensus Decision: {data.get('consensus_decision')}")
            print(f"   Unanimous: {data.get('unanimous')}")
            print(f"   Confidence: {data.get('confidence')}")
        else:
            print(f"❌ Hybrid Analysis Endpoint Error: {response.status_code}")
    except Exception as e:
        print(f"⚠️  Hybrid Analysis Endpoint Unreachable: {str(e)}")

if __name__ == "__main__":
    print("\n" + "="*60)
    print("API ENDPOINT TEST SUITE")
    print("="*60)
    print("Note: Make sure the FastAPI server is running on port 8000")
    print("Run: uvicorn api_server:app --reload --port 8000")
    
    test_prolog_endpoint()
    test_astar_endpoint()
    test_astar_compare_endpoint()
    test_ga_optimize_endpoint()
    test_hybrid_endpoint()
    
    print("\n" + "="*60)
    print("API Test Suite Complete")
    print("="*60)
