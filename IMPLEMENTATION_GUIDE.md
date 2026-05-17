# Smart Access Control System - Implementation Guide

## 🎯 Project Overview

This project implements a **smart passwordless access-control optimizer** that combines multiple AI techniques to improve security, usability, and compliance in access control systems.

### Now Includes Three Advanced Algorithms:

1. **🔴 Prolog Rule Engine** (Original) - Symbolic reasoning with hard constraints
2. **🧠 A* Graph Search** (NEW) - Optimal path finding through access decision graphs
3. **🧬 Genetic Algorithm** (NEW) - Policy optimization through evolutionary computation

---

## 📁 Project Structure

```
Project/
├── app.py                           # Streamlit dashboard (4 new pages added)
├── api_server.py                    # FastAPI server (7 new endpoints)
├── astar_decision_engine.py         # NEW: A* algorithm implementation
├── genetic_policy_optimizer.py      # NEW: Genetic Algorithm implementation
├── test_algorithms.py               # Test suite for algorithms
├── test_api_endpoints.py            # Test suite for API endpoints
├── access_control_authentic_500k.csv # Dataset
├── AIPROJECT.ipynb                  # Jupyter notebook
├── facts.pl                         # Prolog facts
├── rules.pl                         # Prolog rules
└── requirements_api.txt             # Dependencies
```

---

## 🚀 Getting Started

### Prerequisites

```bash
pip install streamlit pandas numpy scikit-learn matplotlib seaborn fastapi uvicorn requests
```

### Running the Application

#### 1. Start the FastAPI Server

```bash
cd "c:\Users\eshan\Downloads\Project"
uvicorn api_server:app --reload --port 8000
```

#### 2. Run the Streamlit Dashboard

```bash
streamlit run app.py
```

The dashboard will open at `http://localhost:8501`

---

## 🧠 Algorithm Details

### 1. A* Graph Search Algorithm

**File:** `astar_decision_engine.py`

**How it works:**
- Builds a directed graph where nodes are access states (role, risk, compliance)
- Uses A* search to find the optimal path from current state to approval
- Heuristic: Euclidean distance to compliant, low-risk state
- Returns: Optimal path, decision, and total cost

**Key Classes:**
- `AccessNode`: Represents a state in the access decision graph
- `AccessGraph`: Builds and manages the access graph
- `AStarEngine`: Implements the A* search algorithm

**Usage:**
```python
from astar_decision_engine import analyze_access_with_astar

user_profile = {
    'role': 'admin',
    'risk_score': 0.3,
    'compliance_status': 'Compliant',
    'failed_login_attempts': 0,
    'access_sensitivity_level': 'Low',
    'time_of_day': 'Morning',
    'department': 'engineering'
}

result = analyze_access_with_astar(user_profile)
# Returns: {
#   'decision': 'approve|deny|review',
#   'algorithm': 'A* Graph Search',
#   'path_length': int,
#   'total_cost': float,
#   'path_description': str,
#   'path_steps': [...]
# }
```

**Advantages:**
- ✅ Finds optimal compliant paths
- ✅ Considers multiple transition options
- ✅ Cost-aware decision making
- ✅ Explains the path taken

---

### 2. Genetic Algorithm for Policy Optimization

**File:** `genetic_policy_optimizer.py`

**How it works:**
- Creates a population of random access control policies
- Each policy is a set of rules with conditions and actions
- Fitness = (Security + Usability - Violations) × Compliance Factor
- Evolves policies through crossover and mutation
- Tournament selection keeps the fittest policies

**Key Classes:**
- `PolicyRule`: Individual rule definition
- `AccessPolicy`: Complete policy (chromosome)
- `GeneticAlgorithmOptimizer`: GA implementation

**GA Parameters:**
- **Population Size**: Number of policies per generation (default: 20)
- **Generations**: Evolution iterations (default: 50)
- **Mutation Rate**: Probability of mutation (default: 0.3)
- **Crossover Rate**: Probability of crossover (default: 0.7)
- **Tournament Size**: Selection tournament size (default: 3)

**Usage:**
```python
from genetic_policy_optimizer import optimize_policies

training_data = [
    {'role': 'admin', 'risk_score': 0.2, 'compliance_status': 'Compliant', ...},
    {'role': 'developer', 'risk_score': 0.4, 'compliance_status': 'Non-Compliant', ...},
    ...
]

result = optimize_policies(training_data, population_size=20, generations=50)
# Returns: {
#   'algorithm': 'Genetic Algorithm',
#   'generations_run': int,
#   'best_fitness_history': [...],
#   'best_policies': [{
#       'rank': int,
#       'fitness_score': float,
#       'num_rules': int,
#       'rules': [...]
#   }],
#   'improvement': float
# }
```

**Advantages:**
- ✅ Finds near-optimal policies
- ✅ Balances security vs usability
- ✅ Evolves automatically from data
- ✅ Shows convergence history
- ✅ Multiple solution candidates

---

## 🎨 Streamlit Dashboard Pages

### Original Pages (Unchanged)
- 🏠 **Dashboard Overview**: KPIs, charts, sample decisions
- 🔍 **Single User Lookup**: Individual user access evaluation
- 📊 **Risk Analytics**: Risk patterns and trends
- ⚙️ **Policy Simulator**: What-if scenario testing

### New Pages (NEW!)

#### 🧠 A* Graph Search
- Configure access request parameters
- Run A* graph search algorithm
- View optimal access path
- See path steps and costs
- Compare to Prolog decisions

**Example Output:**
```
Decision: APPROVE
Path Length: 3 steps
Total Cost: 2.50
Path Description: admin@compliant → admin@compliant → admin@compliant
```

#### 🧬 Genetic Algorithm
- Set optimization parameters (population, generations, sample size)
- Run GA evolution on historical data
- View fitness convergence chart
- Inspect top 5 evolved policies
- See improvement percentage

**Example Output:**
```
Generations: 50
Best Fitness: 0.7842
Improvement: 12.35%
Top Policy: 8 rules, fitness 0.7842
```

#### ⚡ Hybrid AI Analysis
- Run all three algorithms simultaneously
- Compare Prolog vs A* vs GA
- Get consensus decision
- View confidence levels
- Show detailed algorithm breakdown

**Example Output:**
```
Prolog Decision: APPROVE
A* Decision: APPROVE
GA Best Fitness: 0.82

Consensus Decision: APPROVE
Confidence: VERY HIGH
Unanimous: Yes ✅
```

---

## 🔌 API Endpoints

### Existing Endpoints
- `GET /api/summary` - System statistics
- `GET /api/filter-options` - Available filters
- `GET /api/prolog-rules` - Prolog rules
- `POST /api/simulate` - Prolog decision
- `GET /api/user/{user_id}` - User lookup

### NEW A* Endpoints

#### `POST /api/astar-analyze`
Analyze access request using A* graph search
```bash
curl -X POST http://localhost:8000/api/astar-analyze \
  -H "Content-Type: application/json" \
  -d '{
    "role": "admin",
    "risk_score": 0.3,
    "compliance_status": "Compliant",
    ...
  }'
```

#### `POST /api/astar-compare`
Compare Prolog vs A* decisions
```bash
curl -X POST http://localhost:8000/api/astar-compare \
  -H "Content-Type: application/json" \
  -d '{...}'
```

### NEW GA Endpoints

#### `POST /api/ga-optimize-policies`
Run genetic algorithm optimization
```bash
curl -X POST http://localhost:8000/api/ga-optimize-policies
```

#### `GET /api/ga-status`
Check GA optimization status

#### `POST /api/ga-evaluate-policy`
Evaluate user request with evolved policies

### NEW Hybrid Endpoint

#### `POST /api/hybrid-analysis`
Run all three algorithms (Prolog + A* + GA)
```bash
curl -X POST http://localhost:8000/api/hybrid-analysis \
  -H "Content-Type: application/json" \
  -d '{...}'
```

---

## 📊 Testing

### Run Algorithm Tests
```bash
python test_algorithms.py
```

This tests:
- ✅ A* algorithm with sample data
- ✅ Genetic Algorithm with sample data
- ✅ Displays results and recommendations

### Run API Endpoint Tests
```bash
# First start the API server:
uvicorn api_server:app --reload --port 8000

# In another terminal:
python test_api_endpoints.py
```

This tests all 5 new API endpoints.

---

## 🎯 Algorithm Comparison

| Feature | Prolog | A* | GA |
|---------|--------|-----|-----|
| Speed | ⚡ Very Fast | ⚡⚡ Fast | 🐢 Slow (30s+) |
| Accuracy | ✅ High | ✅ High | ✅ Medium-High |
| Explainability | ✅ Excellent | ✅ Good | ⚠️ Complex |
| Optimization | ❌ None | ✅ Path Optimization | ✅ Policy Optimization |
| Real-time | ✅ Yes | ✅ Yes | ❌ No |
| Scalability | ✅ Good | ✅ Good | ⚠️ Limited |
| Rule Evolution | ❌ No | ❌ No | ✅ Yes |

---

## 💡 Use Cases

### When to Use Each Algorithm

**Use Prolog when:**
- You need real-time decisions
- Explainability is critical
- Rules are well-defined and static
- Performance is important

**Use A* when:**
- Users have low initial compliance/high risk
- You want to find optimized access paths
- You need to minimize access cost
- Path explanation is needed

**Use GA when:**
- You want to improve policies from data
- You have historical access logs
- You want to balance security/usability
- Policies need periodic optimization

**Use Hybrid when:**
- You need maximum confidence
- Decision stakes are high
- You can afford computation time
- You want consensus-based decisions

---

## 📈 Performance Metrics

### A* Algorithm
- **Time Complexity**: O(b^d) where b=branching factor, d=depth
- **Space Complexity**: O(b^d)
- **Typical Runtime**: < 100ms
- **Path Optimality**: Guaranteed

### Genetic Algorithm
- **Time Complexity**: O(generations × population × evaluation_cost)
- **Convergence**: 20-50 generations typical
- **Typical Runtime**: 5-30 seconds (depending on data size)
- **Optimality**: Near-optimal (not guaranteed)

---

## 🔐 Security Considerations

1. **Prolog Rules**: Hard constraints ensure compliance
2. **A* Paths**: Cost-aware routing prevents risky transitions
3. **GA Evolution**: Learns from historical data (bias risk)
4. **Hybrid Consensus**: Requires agreement from multiple algorithms

---

## 📝 Configuration

### Modify Algorithm Parameters

**A* Settings** (in `astar_decision_engine.py`):
```python
engine.find_optimal_path(
    start,
    user_profile,
    max_path_length=5  # Adjust search depth
)
```

**GA Settings** (in `genetic_policy_optimizer.py`):
```python
ga = GeneticAlgorithmOptimizer(
    population_size=20,      # More = better but slower
    generations=50,          # More = better convergence
    mutation_rate=0.3,       # Higher = more diversity
    crossover_rate=0.8       # Higher = inheritance emphasis
)
```

---

## 🐛 Troubleshooting

### Issue: A* module not found
**Solution**: Ensure `astar_decision_engine.py` is in the project folder
```bash
ls astar_decision_engine.py  # Check file exists
```

### Issue: GA takes too long
**Solution**: Reduce sample size and generations
```python
optimize_policies(training_data, population_size=10, generations=20)
```

### Issue: API endpoints not responding
**Solution**: Ensure FastAPI server is running
```bash
uvicorn api_server:app --reload --port 8000
```

---

## 📚 References

### A* Algorithm
- Hart, P. E., Nilsson, N. J., & Raphael, B. (1968). A formal basis for the heuristic determination of minimum cost paths.

### Genetic Algorithm
- Holland, J. H. (1975). Adaptation in Natural and Artificial Systems.
- Goldberg, D. E. (1989). Genetic Algorithms in Search, Optimization, and Machine Learning.

---

## 👥 Team

- Esha Ashfaq
- Hadiqa Mehmood
- CSL-411 AI Lab · Spring 2026

---

## 📄 License

This project is part of the CSL-411 AI Lab coursework. All rights reserved.

---

## 🎓 Learning Outcomes

After completing this project, you should understand:
- ✅ Graph-based access decision modeling
- ✅ A* search algorithm and heuristics
- ✅ Genetic algorithms and evolutionary computation
- ✅ Policy optimization using evolutionary techniques
- ✅ Hybrid AI systems combining multiple algorithms
- ✅ RESTful API design and FastAPI
- ✅ Streamlit dashboard development
- ✅ Access control and security best practices

---

**Last Updated**: May 10, 2026
