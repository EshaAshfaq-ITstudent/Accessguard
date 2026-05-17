#!/usr/bin/env python3
"""Test script for A* and GA implementations"""

import sys
import json

# Test A* Algorithm
print("=" * 60)
print("Testing A* Graph Search Algorithm")
print("=" * 60)

try:
    from astar_decision_engine import analyze_access_with_astar
    
    test_user = {
        'role': 'admin',
        'risk_score': 0.3,
        'compliance_status': 'Compliant',
        'failed_login_attempts': 0,
        'access_sensitivity_level': 'Low',
        'time_of_day': 'Morning',
        'department': 'engineering'
    }
    
    result = analyze_access_with_astar(test_user)
    
    print(f"✅ A* Algorithm Test PASSED")
    print(f"   Decision: {result['decision'].upper()}")
    print(f"   Algorithm: {result['algorithm']}")
    print(f"   Path Length: {result['path_length']} steps")
    print(f"   Total Cost: {result['total_cost']:.3f}")
    print(f"   Recommendation: {result['recommendation']}")
    print()
    
except Exception as e:
    print(f"❌ A* Algorithm Test FAILED")
    print(f"   Error: {str(e)}")
    print()
    sys.exit(1)

# Test Genetic Algorithm
print("=" * 60)
print("Testing Genetic Algorithm")
print("=" * 60)

try:
    from genetic_policy_optimizer import optimize_policies
    import pandas as pd
    
    # Create small test dataset
    test_data = [
        {
            'role': 'admin', 'risk_score': 0.2, 'compliance_status': 'Compliant',
            'failed_login_attempts': 0, 'access_sensitivity_level': 'Low', 'department': 'it'
        },
        {
            'role': 'developer', 'risk_score': 0.4, 'compliance_status': 'Non-Compliant',
            'failed_login_attempts': 1, 'access_sensitivity_level': 'Medium', 'department': 'engineering'
        },
        {
            'role': 'guest', 'risk_score': 0.7, 'compliance_status': 'Non-Compliant',
            'failed_login_attempts': 3, 'access_sensitivity_level': 'High', 'department': 'finance'
        },
    ] * 10  # Repeat to have more data
    
    result = optimize_policies(test_data, population_size=10, generations=10)
    
    print(f"✅ Genetic Algorithm Test PASSED")
    print(f"   Algorithm: {result['algorithm']}")
    print(f"   Generations Run: {result['generations_run']}")
    print(f"   Final Population Size: {result['final_population_size']}")
    print(f"   Best Fitness: {result['best_policies'][0]['fitness_score']:.4f}")
    print(f"   Policies Evolved: {len(result['best_policies'])}")
    print(f"   Improvement: {result['improvement']:.2f}%")
    print(f"   Recommendation: {result['recommendation']}")
    print()
    
except Exception as e:
    print(f"❌ Genetic Algorithm Test FAILED")
    print(f"   Error: {str(e)}")
    print()
    sys.exit(1)

print("=" * 60)
print("✅ ALL TESTS PASSED")
print("=" * 60)
print("\nBoth A* and Genetic Algorithm implementations are working!")
print("You can now run the Streamlit app to use these algorithms.")
