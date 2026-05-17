🔐 AccessGuard — Smart Passwordless Access Control Policy Optimizer
An AI-powered cybersecurity system for intelligent, adaptive, and passwordless access-control decision making.

📌 Overview
AccessGuard is an intelligent passwordless access-control system designed to enhance modern cybersecurity infrastructures using Artificial Intelligence techniques.
Traditional authentication systems rely heavily on static passwords and fixed policies, making them vulnerable to:
Password theft
Phishing attacks
Credential reuse
Static rule limitations
Poor behavioral analysis

This project introduces an adaptive AI-driven solution capable of:
✅ Predicting access risks
✅ Optimizing security policies
✅ Evaluating secure access paths
✅ Simulating decision scenarios
✅ Enforcing intelligent rule-based access control

The system combines:
🧠 Machine Learning
🔎 A* Search Algorithm
🧬 Genetic Algorithms
📜 Prolog Rule-Based Logic
⚛️ React Front-End
🚀 FastAPI Backend
✨ Key Features
🔐 Intelligent Passwordless Authentication

AI-based adaptive access decisions without traditional password dependency.
📊 Risk Prediction & Analytics
Predict suspicious access attempts using machine learning models.
🔎 A* Search Decision Engine
Graph-based path optimization for secure access decisions.
🧬 Genetic Algorithm Optimization
Automatically evolves and improves security policies.
📜 Prolog Rule-Based Enforcement
Implements logical security rules using Prolog facts and predicates.
📈 Interactive Dashboard
Modern visualization and simulation dashboard using Streamlit and React.
⚡ FastAPI Backend
High-performance API services for analytics and simulations.
🧪 Scenario Simulation
Run what-if analysis and compare hybrid AI decision approaches.

🧠 AI Concepts Implemented
Concept	Purpose
Machine Learning	Risk prediction
A* Search	Secure path evaluation
Genetic Algorithm	Policy optimization
Prolog Logic	Rule-based access enforcement
Hybrid AI Analysis	Comparative intelligent decision-making

🏗️ System Architecture
User Request
      ↓
Risk Prediction (ML)
      ↓
Rule Evaluation (Prolog)
      ↓
A* Path Analysis
      ↓
Genetic Policy Optimization
      ↓
Decision Simulation
      ↓
Dashboard Visualization

🛠️ Tech Stack
Backend
Python
FastAPI
Pandas
NumPy
Scikit-learn
Frontend
React.js
Vite
Streamlit
AI & Algorithms
Machine Learning
Genetic Algorithms
A* Search
Prolog Rule Engine
Visualization
Matplotlib
Seaborn
Streamlit Charts

📂 Project Structure
AccessGuard/
│
├── app.py
├── api_server.py
├── astar_decision_engine.py
├── genetic_policy_optimizer.py
├── facts.pl
├── rules.pl
├── access_control_authentic_500k.csv
├── requirements_api.txt
├── test_algorithms.py
├── test_api_endpoints.py
│
├── accessguard-ui/
│   ├── src/
│   ├── public/
│   ├── package.json
│   └── vite.config.js
│
└── Project/

🚀 Installation Guide
1️⃣ Clone Repository
git clone https://github.com/EshaAshfaq-ITstudent/Accessguard.git
cd Accessguard

📦 Install Dependencies
Backend Dependencies
pip install -r requirements_api.txt
Additional Packages
pip install streamlit numpy matplotlib seaborn pydantic
Optional Prolog Integration
pip install pyswip
⚛️ Setup React Front-End
cd accessguard-ui
npm install
▶️ Run the Backend API
uvicorn api_server:app --reload --port 8000

API will run on:
http://127.0.0.1:8000

📊 Run Streamlit Dashboard
streamlit run app.py

🌐 Run React Front-End
cd accessguard-ui
npm run dev

🔌 API Endpoints
Method	Endpoint	Description
GET	/api/summary	Dataset summary
GET	/api/filter-options	Filter values
GET	/api/prolog-rules	Prolog rule reference
GET	/api/decisions-week	Weekly decisions
GET	/api/analytics	Analytics charts
GET	/api/risk-trend	Risk trends
GET	/api/dashboard-data	Dashboard metrics
GET	/api/risk-composition	Risk breakdown
GET	/api/timeline	Timeline events
GET	/api/user/{user_id}	User access data
GET	/api/sample-decisions	Sample records
POST	/api/simulate	Simulate decision logic
POST	/api/astar-analyze	A* analysis
POST	/api/astar-compare	Compare decision systems
POST	/api/ga-optimize-policies	Optimize policies
GET	/api/ga-status	GA progress
POST	/api/ga-evaluate-policy	Evaluate policy
POST	/api/hybrid-analysis	Hybrid AI analysis

📸 Dashboard Modules
📊 Dashboard Overview
👤 Single User Lookup
⚠️ Risk Analytics
🧪 Policy Simulator
🔎 A* Graph Search
🧬 Genetic Algorithm Analysis
🤖 Hybrid AI Decision Comparison
🎯 Project Objectives

Build a smart passwordless access-control system
Predict risk using machine learning
Optimize policies using Genetic Algorithms
Enforce logical rules using Prolog
Provide visualization and simulation tools
Improve adaptive cybersecurity decision making
🔮 Future Enhancements
🔐 Biometric Authentication
☁️ Cloud Deployment
📱 Mobile Application
🚨 Real-Time Intrusion Detection
🧠 Deep Learning Anomaly Detection
🏢 Enterprise Security Integration

📚 References
Artificial Intelligence: A Modern Approach — Russell & Norvig
Machine Learning — Tom Mitchell
Genetic Algorithms — David Goldberg
Scikit-learn Documentation
React Documentation
SWI-Prolog Documentation

👩‍💻 Authors
Esha Ashfaq
Hadiqa Mehmood

🏫 Academic Information
Bahria University Karachi Campus
Department of Computer Science
BS IT 6A — Spring 2026
Course: Artificial Intelligence Lab (CSL-411)

📜 License
This project is developed for academic and research purposes.

⭐ Support

If you like this project:
⭐ Star the repository
🍴 Fork the project
🛠️ Contribute improvements
🐛 Report issues
