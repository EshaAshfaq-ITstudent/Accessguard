import { useEffect, useMemo, useRef, useState } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import {
  Area,
  AreaChart,
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  Pie,
  PieChart,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
  LineChart,
  Line,
} from 'recharts'
import {
  FiActivity,
  FiAlertTriangle,
  FiCheckCircle,
  FiClock,
  FiCpu,
  FiDatabase,
  FiSearch,
  FiShield,
  FiUser,
  FiXCircle,
  FiTrendingUp,
  FiGitBranch,
  FiZap,
} from 'react-icons/fi'
import './App.css'

const API_BASE = import.meta.env.VITE_API_BASE ?? 'http://127.0.0.1:8000'

const fallback = {
  summary: { total_records: 500000, unique_users: 4286, grant_rate: 81.3, anomaly_rate: 11.8 },
  decisionsByDay: [
    { day: 'Mon', approve: 146, review: 28, deny: 9 },
    { day: 'Tue', approve: 158, review: 25, deny: 13 },
    { day: 'Wed', approve: 172, review: 30, deny: 11 },
    { day: 'Thu', approve: 165, review: 35, deny: 16 },
    { day: 'Fri', approve: 150, review: 33, deny: 18 },
    { day: 'Sat', approve: 119, review: 31, deny: 17 },
    { day: 'Sun', approve: 108, review: 22, deny: 10 },
  ],
  riskDistribution: [
    { name: 'Low Risk', value: 59, color: '#27d07d' },
    { name: 'Medium Risk', value: 29, color: '#f4b844' },
    { name: 'High Risk', value: 12, color: '#ff5a6f' },
  ],
  timeline: [],
  analytics: {
    grant_by_compliance: [],
    anomaly_by_time: [],
    grant_by_role: [],
    grant_by_department: [],
  },
  prologRules: [],
  sampleRows: [],
}

const MIN_BOOT_MS = 1200

function BootScreen() {
  return (
    <motion.div
      className="boot-screen"
      role="presentation"
      initial={{ opacity: 1 }}
      exit={{ opacity: 0, pointerEvents: 'none' }}
      transition={{ duration: 0.55, ease: [0.22, 1, 0.36, 1] }}
    >
      <div className="boot-screen__grain" aria-hidden />
      <div className="boot-screen__pulse" aria-hidden />
      <div className="boot-screen__content">
        <div className="boot-screen__mark">
          <motion.div
            className="boot-screen__orbit"
            animate={{ rotate: 360 }}
            transition={{ repeat: Infinity, duration: 2.4, ease: 'linear' }}
            aria-hidden
          />
          <motion.div
            className="boot-screen__icon"
            animate={{ y: [0, -8, 0] }}
            transition={{ repeat: Infinity, duration: 2.8, ease: 'easeInOut' }}
          >
            <FiShield />
          </motion.div>
        </div>
        <p className="boot-screen__title">
          AccessGuard
        </p>
        <p className="boot-screen__sub">Hydrating dashboards &amp; policy context…</p>
        <div className="boot-screen__tracks" aria-hidden>
          {[0.15, 0.28, 0.52, 0.38, 0.62].map((w, i) => (
            <motion.span
              key={`boot-bar-${i}`}
              className="boot-screen__bar"
              style={{ transformOrigin: 'left center', width: `${Math.round(w * 100)}%` }}
              initial={{ opacity: 0.35, scaleX: 0.55 }}
              animate={{ opacity: [0.38, 0.92, 0.48], scaleX: [0.58, 1, 0.72] }}
              transition={{ repeat: Infinity, duration: 1.85 + i * 0.12, delay: i * 0.06, ease: 'easeInOut' }}
            />
          ))}
        </div>
      </div>
    </motion.div>
  )
}

function App() {
  const [activeTab, setActiveTab] = useState('dashboard')
  const [summary, setSummary] = useState(fallback.summary)
  const [decisionsByDay, setDecisionsByDay] = useState(fallback.decisionsByDay)
  const [riskDistribution, setRiskDistribution] = useState(fallback.riskDistribution)
  const [timeline, setTimeline] = useState(fallback.timeline)
  const [analytics, setAnalytics] = useState(fallback.analytics)
  const [prologRules, setPrologRules] = useState(fallback.prologRules)
  const [sampleRows, setSampleRows] = useState(fallback.sampleRows)
  const [apiOnline, setApiOnline] = useState(false)
  const [riskTrend, setRiskTrend] = useState([])
  const [filterOptions, setFilterOptions] = useState({ roles: [], departments: [], sensitivities: [], compliances: [] })
  const [filters, setFilters] = useState({ role: '', department: '', sensitivity: '', compliance: '' })
  const [autoRefresh, setAutoRefresh] = useState(false)
  const [lastUpdated, setLastUpdated] = useState('')

  const [lookupUserId, setLookupUserId] = useState('')
  const [lookupResult, setLookupResult] = useState(null)
  const [lookupMsg, setLookupMsg] = useState('Enter a user ID and click search.')

  const [formData, setFormData] = useState({
    role: 'developer',
    risk: 0.34,
    failed: 1,
    compliance: 'Compliant',
    sensitivity: 'Medium',
    time: 'Afternoon',
  })
  const [simResult, setSimResult] = useState({ decision: 'REVIEW', reason: 'Awaiting simulation run.' })
  const [bootVisible, setBootVisible] = useState(true)
  const bootRevealScheduled = useRef(false)

  // A* State
  const [astarFormData, setAstarFormData] = useState({
    role: 'admin',
    risk: 0.3,
    failed: 0,
    compliance: 'Compliant',
    sensitivity: 'Low',
    time: 'Morning',
  })
  const [astarResult, setAstarResult] = useState(null)
  const [astarLoading, setAstarLoading] = useState(false)

  // GA State
  const [gaFormData, setGaFormData] = useState({
    popSize: 20,
    generations: 50,
    sampleSize: 500,
  })
  const [gaResult, setGaResult] = useState(null)
  const [gaLoading, setGaLoading] = useState(false)

  // Hybrid State
  const [hybridFormData, setHybridFormData] = useState({
    role: 'analyst',
    risk: 0.45,
    failed: 1,
    compliance: 'Compliant',
    sensitivity: 'High',
    time: 'Morning',
  })
  const [hybridResult, setHybridResult] = useState(null)
  const [hybridLoading, setHybridLoading] = useState(false)

  useEffect(() => {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([k, v]) => {
      if (v) params.set(k, v)
    })
    const query = params.toString()
    const loadDashboard = async () => {
      const started = Date.now()
      try {
        const responses = await Promise.all([
          fetch(`${API_BASE}/api/dashboard-data${query ? `?${query}` : ''}`),
          fetch(`${API_BASE}/api/risk-trend`),
          fetch(`${API_BASE}/api/filter-options`),
          fetch(`${API_BASE}/api/prolog-rules`),
        ])
        if (responses.some((res) => !res.ok)) throw new Error('One or more API calls failed.')
        const [dashboardJson, trendJson, optionsJson, rulesJson] = await Promise.all(
          responses.map((res) => res.json()),
        )
        setSummary(dashboardJson.summary)
        setDecisionsByDay(dashboardJson.decisions_week)
        setRiskDistribution(dashboardJson.risk_composition)
        setTimeline(dashboardJson.timeline)
        setAnalytics(dashboardJson.analytics)
        setPrologRules(rulesJson.rules ?? [])
        setSampleRows(dashboardJson.sample_decisions ?? [])
        setRiskTrend(trendJson ?? [])
        setFilterOptions(optionsJson ?? { roles: [], departments: [], sensitivities: [], compliances: [] })
        setLastUpdated(dashboardJson.updated_at ? new Date(dashboardJson.updated_at).toLocaleTimeString() : '')
        setApiOnline(true)
      } catch {
        setApiOnline(false)
      } finally {
        if (!bootRevealScheduled.current) {
          bootRevealScheduled.current = true
          const remaining = Math.max(0, MIN_BOOT_MS - (Date.now() - started))
          window.setTimeout(() => setBootVisible(false), remaining)
        }
      }
    }

    loadDashboard()
    if (!autoRefresh) return undefined
    const timer = setInterval(loadDashboard, 15000)
    return () => clearInterval(timer)
  }, [filters, autoRefresh])

  useEffect(() => {
    document.body.style.overflow = bootVisible ? 'hidden' : ''
    return () => {
      document.body.style.overflow = ''
    }
  }, [bootVisible])

  const decisionTone = useMemo(() => {
    const value = simResult.decision.toUpperCase()
    if (value === 'APPROVE') return { tone: 'safe', icon: <FiCheckCircle /> }
    if (value === 'DENY') return { tone: 'danger', icon: <FiXCircle /> }
    return { tone: 'warn', icon: <FiAlertTriangle /> }
  }, [simResult.decision])

  const onFormChange = (key, value) => setFormData((prev) => ({ ...prev, [key]: value }))

  const runSimulation = async () => {
    try {
      const response = await fetch(`${API_BASE}/api/simulate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          role: formData.role,
          department: 'engineering',
          risk_score: formData.risk,
          compliance_status: formData.compliance,
          failed_login_attempts: formData.failed,
          access_sensitivity_level: formData.sensitivity,
          time_of_day: formData.time,
        }),
      })
      if (!response.ok) throw new Error('Simulation failed.')
      setSimResult(await response.json())
    } catch {
      setSimResult({ decision: 'REVIEW', reason: 'Could not contact API. Start backend and retry.' })
    }
  }

  const runLookup = async () => {
    if (!lookupUserId.trim()) {
      setLookupMsg('Please enter a user ID first.')
      setLookupResult(null)
      return
    }
    try {
      const response = await fetch(`${API_BASE}/api/user/${encodeURIComponent(lookupUserId.trim())}`)
      if (!response.ok) throw new Error('Lookup failed.')
      const data = await response.json()
      if (!data.found) {
        setLookupMsg(`User ${lookupUserId.trim()} not found in dataset.`)
        setLookupResult(null)
        return
      }
      setLookupResult(data.user)
      setLookupMsg('User found.')
    } catch {
      setLookupMsg('API unavailable. Start backend and retry.')
      setLookupResult(null)
    }
  }

  // A* Algorithm
  const runAstarAnalysis = async () => {
    setAstarLoading(true)
    try {
      const response = await fetch(`${API_BASE}/api/astar-analyze`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          role: astarFormData.role,
          department: 'engineering',
          risk_score: astarFormData.risk,
          compliance_status: astarFormData.compliance,
          failed_login_attempts: astarFormData.failed,
          access_sensitivity_level: astarFormData.sensitivity,
          time_of_day: astarFormData.time,
        }),
      })
      if (!response.ok) throw new Error('A* analysis failed.')
      setAstarResult(await response.json())
    } catch {
      setAstarResult({ decision: 'REVIEW', error: 'Could not contact API. Start backend and retry.' })
    } finally {
      setAstarLoading(false)
    }
  }

  // Genetic Algorithm
  const runGaOptimization = async () => {
    setGaLoading(true)
    try {
      const response = await fetch(`${API_BASE}/api/ga-optimize-policies`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
      })
      if (!response.ok) throw new Error('GA optimization failed.')
      setGaResult(await response.json())
    } catch {
      setGaResult({ algorithm: 'Genetic Algorithm', error: 'Could not contact API. Start backend and retry.' })
    } finally {
      setGaLoading(false)
    }
  }

  // Hybrid Analysis
  const runHybridAnalysis = async () => {
    setHybridLoading(true)
    try {
      const response = await fetch(`${API_BASE}/api/hybrid-analysis`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          role: hybridFormData.role,
          department: 'engineering',
          risk_score: hybridFormData.risk,
          compliance_status: hybridFormData.compliance,
          failed_login_attempts: hybridFormData.failed,
          access_sensitivity_level: hybridFormData.sensitivity,
          time_of_day: hybridFormData.time,
        }),
      })
      if (!response.ok) throw new Error('Hybrid analysis failed.')
      setHybridResult(await response.json())
    } catch {
      setHybridResult({ consensus_decision: 'REVIEW', error: 'Could not contact API. Start backend and retry.' })
    } finally {
      setHybridLoading(false)
    }
  }

  const tabs = [
    { id: 'dashboard', label: 'Dashboard Overview' },
    { id: 'lookup', label: 'Single User Lookup' },
    { id: 'risk', label: 'Risk Analytics' },
    { id: 'simulator', label: 'Policy Simulator' },
    { id: 'astar', label: 'A* Graph Search' },
    { id: 'genetic', label: 'Genetic Algorithm' },
    { id: 'hybrid', label: 'Hybrid AI Analysis' },
  ]

  const updateFilter = (key, value) => {
    setFilters((prev) => ({ ...prev, [key]: value }))
  }

  return (
    <>
      <AnimatePresence mode="wait">
        {bootVisible ? <BootScreen key="accessguard-boot" /> : null}
      </AnimatePresence>
      <div className="dashboard" aria-busy={bootVisible} aria-hidden={bootVisible}>
      <motion.header
        className="hero"
        initial={{ opacity: 0, y: 22 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.6, ease: [0.22, 1, 0.36, 1] }}
      >
        <img
          className="hero-bg"
          src="https://images.pexels.com/photos/5380664/pexels-photo-5380664.jpeg?auto=compress&cs=tinysrgb&w=1400"
          alt=""
          decoding="async"
        />
        <div className="hero-overlay" aria-hidden />
        <div className="hero-content">
          <div className="hero-meta">
            <p className="tag">Passwordless Access Policy Center</p>
            <span className={`status-dot ${apiOnline ? 'live' : ''}`}>{apiOnline ? 'API connected' : 'Offline — sample data'}</span>
          </div>
          <h1>AccessGuard Dashboard</h1>
          <p>Streamlit structure matched in tabs, with modern visuals and live backend data.</p>
        </div>
      </motion.header>

      <section className="kpis" aria-label="Key metrics">
        {[
          { icon: <FiShield />, title: 'Grant Rate', value: `${summary.grant_rate.toFixed(1)}%` },
          { icon: <FiActivity />, title: 'Anomaly Rate', value: `${summary.anomaly_rate.toFixed(1)}%` },
          { icon: <FiUser />, title: 'Unique Users', value: `${summary.unique_users.toLocaleString()}` },
          { icon: <FiDatabase />, title: 'Data Source', value: apiOnline ? 'Live CSV + API' : 'Fallback Mode' },
          { icon: <FiCpu />, title: 'Policy API', value: apiOnline ? 'Online' : 'Offline' },
        ].map((card, idx) => (
          <motion.div
            className="kpi-card"
            key={card.title}
            initial={{ opacity: 0, y: 18 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: idx * 0.06, type: 'spring', stiffness: 320, damping: 26 }}
            whileHover={{
              y: -8,
              rotateX: 5,
              rotateY: idx % 2 === 0 ? -4 : 4,
              scale: 1.03,
              boxShadow: '0 18px 48px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(120, 200, 255, 0.12)',
              transition: { type: 'spring', stiffness: 440, damping: 22 },
            }}
            whileTap={{ scale: 0.985 }}
            style={{ transformStyle: 'preserve-3d' }}
          >
            <div className="kpi-icon">{card.icon}</div>
            <p>{card.title}</p>
            <h3>{card.value}</h3>
          </motion.div>
        ))}
      </section>

      <nav className="tabs" role="tablist" aria-label="Dashboard sections">
        {tabs.map((tab) => (
          <motion.button
            key={tab.id}
            id={`tab-${tab.id}`}
            role="tab"
            type="button"
            className={activeTab === tab.id ? 'tab active' : 'tab'}
            aria-selected={activeTab === tab.id}
            aria-controls={`panel-${tab.id}`}
            tabIndex={activeTab === tab.id ? 0 : -1}
            onClick={() => setActiveTab(tab.id)}
            whileHover={{ y: -3, transition: { type: 'spring', stiffness: 520, damping: 28 } }}
            whileTap={{ scale: 0.97 }}
          >
            {tab.label}
          </motion.button>
        ))}
      </nav>

      <section className="panel filter-panel" aria-labelledby="filters-heading">
        <div className="panel-head">
          <h2 id="filters-heading">Global Filters</h2>
          <small className="meta">Last updated: {lastUpdated || 'N/A'}</small>
        </div>
        <div className="filters">
          <select value={filters.role} onChange={(e) => updateFilter('role', e.target.value)}>
            <option value="">All Roles</option>
            {filterOptions.roles.map((x) => <option key={x}>{x}</option>)}
          </select>
          <select value={filters.department} onChange={(e) => updateFilter('department', e.target.value)}>
            <option value="">All Departments</option>
            {filterOptions.departments.map((x) => <option key={x}>{x}</option>)}
          </select>
          <select value={filters.sensitivity} onChange={(e) => updateFilter('sensitivity', e.target.value)}>
            <option value="">All Sensitivities</option>
            {filterOptions.sensitivities.map((x) => <option key={x}>{x}</option>)}
          </select>
          <select value={filters.compliance} onChange={(e) => updateFilter('compliance', e.target.value)}>
            <option value="">All Compliance</option>
            {filterOptions.compliances.map((x) => <option key={x}>{x}</option>)}
          </select>
          <label className="refresh-toggle">
            <input type="checkbox" checked={autoRefresh} onChange={(e) => setAutoRefresh(e.target.checked)} />
            Auto refresh (15s)
          </label>
        </div>
      </section>

      <main>
      <AnimatePresence mode="wait">
      {activeTab === 'dashboard' && (
        <motion.div
          key="dashboard"
          id="panel-dashboard"
          role="tabpanel"
          aria-labelledby="tab-dashboard"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2>Weekly Access Decisions</h2></div>
              <ResponsiveContainer width="100%" height={260}>
                <AreaChart data={decisionsByDay}>
                  <defs>
                    <linearGradient id="approve" x1="0" y1="0" x2="0" y2="1"><stop offset="5%" stopColor="#2ddc86" stopOpacity={0.5} /><stop offset="95%" stopColor="#2ddc86" stopOpacity={0} /></linearGradient>
                    <linearGradient id="review" x1="0" y1="0" x2="0" y2="1"><stop offset="5%" stopColor="#f4b844" stopOpacity={0.35} /><stop offset="95%" stopColor="#f4b844" stopOpacity={0} /></linearGradient>
                  </defs>
                  <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" />
                  <XAxis dataKey="day" stroke="#91a4cc" />
                  <YAxis stroke="#91a4cc" />
                  <Tooltip />
                  <Area type="monotone" dataKey="approve" stroke="#2ddc86" fillOpacity={1} fill="url(#approve)" />
                  <Area type="monotone" dataKey="review" stroke="#f4b844" fillOpacity={1} fill="url(#review)" />
                </AreaChart>
              </ResponsiveContainer>
            </article>

            <article className="panel">
              <div className="panel-head"><h2>Risk Composition</h2></div>
              <ResponsiveContainer width="100%" height={260}>
                <PieChart>
                  <Pie data={riskDistribution} dataKey="value" nameKey="name" innerRadius={55} outerRadius={90}>
                    {riskDistribution.map((entry) => <Cell key={entry.name} fill={entry.color} />)}
                  </Pie>
                  <Tooltip />
                </PieChart>
              </ResponsiveContainer>
              <div className="legend">
                {riskDistribution.map((item) => <span key={item.name}><i style={{ background: item.color }} /> {item.name}</span>)}
              </div>
            </article>
          </section>

          <section className="panel">
            <div className="panel-head"><h2>Sample Access Decisions</h2></div>
            <div className="table-wrap">
              <table>
                <thead><tr><th>User</th><th>Role</th><th>Department</th><th>Risk</th><th>Compliance</th><th>Decision</th></tr></thead>
                <tbody>
                  {sampleRows.map((row) => (
                    <tr key={row.user_id}>
                      <td>{row.user_id}</td><td>{row.role}</td><td>{row.department}</td><td>{row.risk_score}</td><td>{row.compliance_status}</td>
                      <td><span className={`status ${row.decision.toLowerCase()}`}>{row.decision}</span></td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </section>

          <section className="panel">
            <div className="panel-head"><h2>Risk Trend (12 Periods)</h2></div>
            <ResponsiveContainer width="100%" height={250}>
              <AreaChart data={riskTrend}>
                <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" />
                <XAxis dataKey="period" stroke="#91a4cc" />
                <YAxis stroke="#91a4cc" />
                <Tooltip />
                <Area type="monotone" dataKey="avg_risk" stroke="#ff8f6b" fill="#ff8f6b22" />
                <Area type="monotone" dataKey="anomaly_rate" stroke="#f4b844" fill="#f4b84422" />
              </AreaChart>
            </ResponsiveContainer>
          </section>
        </motion.div>
      )}

      {activeTab === 'lookup' && (
        <motion.div
          key="lookup"
          id="panel-lookup"
          role="tabpanel"
          aria-labelledby="tab-lookup"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
        <section className="grid">
          <article className="panel">
            <div className="panel-head"><h2>Single User Lookup</h2></div>
            <div className="lookup">
              <input type="text" value={lookupUserId} onChange={(e) => setLookupUserId(e.target.value)} placeholder="Enter user id (e.g., U00015)" />
              <button type="button" onClick={runLookup}><FiSearch /> Search</button>
            </div>
            <p className="sim-reason">{lookupMsg}</p>
            {lookupResult && (
              <div className="lookup-card">
                <p><strong>User:</strong> {lookupResult.user_id}</p>
                <p><strong>Role:</strong> {lookupResult.role}</p>
                <p><strong>Department:</strong> {lookupResult.department}</p>
                <p><strong>Risk:</strong> {lookupResult.risk_score}</p>
                <p><strong>Compliance:</strong> {lookupResult.compliance_status}</p>
                <p><strong>Decision:</strong> {lookupResult.decision}</p>
                <p><strong>Reason:</strong> {lookupResult.reason}</p>
                <p><strong>Matched Rule:</strong> {lookupResult.matched_rule}</p>
              </div>
            )}
          </article>

          <article className="panel">
            <div className="panel-head"><h2>Active Prolog Rules</h2></div>
            <div className="rules">
              {prologRules.map((rule) => <code key={rule}>{rule}</code>)}
            </div>
          </article>
        </section>
        </motion.div>
      )}

      {activeTab === 'risk' && (
        <motion.div
          key="risk"
          id="panel-risk"
          role="tabpanel"
          aria-labelledby="tab-risk"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2>Grant Rate by Compliance</h2></div>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={analytics.grant_by_compliance}>
                  <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" /><XAxis dataKey="label" stroke="#91a4cc" /><YAxis stroke="#91a4cc" /><Tooltip />
                  <Bar dataKey="value" fill="#4fc3f7" radius={[6, 6, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </article>
            <article className="panel">
              <div className="panel-head"><h2>Anomaly Rate by Time</h2></div>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={analytics.anomaly_by_time}>
                  <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" /><XAxis dataKey="label" stroke="#91a4cc" /><YAxis stroke="#91a4cc" /><Tooltip />
                  <Bar dataKey="value" fill="#f39c12" radius={[6, 6, 0, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </article>
          </section>
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2>Grant Rate by Role</h2></div>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={analytics.grant_by_role} layout="vertical">
                  <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" /><XAxis type="number" stroke="#91a4cc" /><YAxis dataKey="label" type="category" stroke="#91a4cc" width={100} /><Tooltip />
                  <Bar dataKey="value" fill="#9b7bff" radius={[0, 6, 6, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </article>
            <article className="panel">
              <div className="panel-head"><h2>Grant Rate by Department</h2></div>
              <ResponsiveContainer width="100%" height={220}>
                <BarChart data={analytics.grant_by_department} layout="vertical">
                  <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" /><XAxis type="number" stroke="#91a4cc" /><YAxis dataKey="label" type="category" stroke="#91a4cc" width={100} /><Tooltip />
                  <Bar dataKey="value" fill="#48c9b0" radius={[0, 6, 6, 0]} />
                </BarChart>
              </ResponsiveContainer>
            </article>
          </section>
        </motion.div>
      )}

      {activeTab === 'simulator' && (
        <motion.div
          key="simulator"
          id="panel-simulator"
          role="tabpanel"
          aria-labelledby="tab-simulator"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
        <section className="grid">
          <article className="panel">
            <div className="panel-head"><h2>Policy Simulator</h2><span className={`status ${decisionTone.tone}`}>{simResult.decision}</span></div>
            <div className="simulator">
              <label>Role<select value={formData.role} onChange={(e) => onFormChange('role', e.target.value)}><option>developer</option><option>admin</option><option>manager</option><option>analyst</option></select></label>
              <label className="sim-range-field">
                Risk Score: {formData.risk.toFixed(2)}
                <span className="sim-range-shell">
                  <span
                    className="sim-range-fill"
                    style={{
                      transform: `scaleX(${Math.max(formData.risk, 0.025)})`,
                      transformOrigin: 'left center',
                    }}
                  />
                  <input
                    className="sim-range-input"
                    type="range"
                    min="0"
                    max="1"
                    step="0.01"
                    value={formData.risk}
                    onChange={(e) => onFormChange('risk', Number(e.target.value))}
                  />
                </span>
              </label>
              <label>Failed Attempts<input type="number" min="0" max="10" value={formData.failed} onChange={(e) => onFormChange('failed', Number(e.target.value))} /></label>
              <label>Compliance<select value={formData.compliance} onChange={(e) => onFormChange('compliance', e.target.value)}><option>Compliant</option><option>Non-Compliant</option></select></label>
              <label>Sensitivity<select value={formData.sensitivity} onChange={(e) => onFormChange('sensitivity', e.target.value)}><option>Low</option><option>Medium</option><option>High</option></select></label>
              <label>Time<select value={formData.time} onChange={(e) => onFormChange('time', e.target.value)}><option>Morning</option><option>Afternoon</option><option>Evening</option><option>Night</option></select></label>
            </div>
            <div className={`decision ${decisionTone.tone}`}>{decisionTone.icon}<strong>{simResult.decision}</strong></div>
            <p className="sim-reason">{simResult.reason}</p>
            <button className="sim-btn" type="button" onClick={runSimulation}>Run Simulation</button>
          </article>

          <article className="panel">
            <div className="panel-head"><h2>Recent Access Activity</h2></div>
            <div className="timeline">
              {timeline.map((item) => (
                <div className="event" key={`${item.user}-${item.time}`}>
                  <FiClock />
                  <div><p><strong>{item.user}</strong> - {item.event}</p><small>{item.time}</small></div>
                  <span className={`status ${item.status.toLowerCase()}`}>{item.status}</span>
                </div>
              ))}
            </div>
          </article>
        </section>
        </motion.div>
      )}

      {activeTab === 'astar' && (
        <motion.div
          key="astar"
          id="panel-astar"
          role="tabpanel"
          aria-labelledby="tab-astar"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2><FiTrendingUp style={{ marginRight: '8px' }} />A* Graph Search Analysis</h2></div>
              <div className="simulator">
                <label>Role<select value={astarFormData.role} onChange={(e) => setAstarFormData({...astarFormData, role: e.target.value})}><option>developer</option><option>admin</option><option>manager</option><option>analyst</option></select></label>
                <label className="sim-range-field">
                  Risk Score: {astarFormData.risk.toFixed(2)}
                  <span className="sim-range-shell">
                    <span className="sim-range-fill" style={{transform: `scaleX(${Math.max(astarFormData.risk, 0.025)})`, transformOrigin: 'left center'}} />
                    <input className="sim-range-input" type="range" min="0" max="1" step="0.01" value={astarFormData.risk} onChange={(e) => setAstarFormData({...astarFormData, risk: Number(e.target.value)})} />
                  </span>
                </label>
                <label>Failed Attempts<input type="number" min="0" max="10" value={astarFormData.failed} onChange={(e) => setAstarFormData({...astarFormData, failed: Number(e.target.value)})} /></label>
                <label>Compliance<select value={astarFormData.compliance} onChange={(e) => setAstarFormData({...astarFormData, compliance: e.target.value})}><option>Compliant</option><option>Non-Compliant</option></select></label>
                <label>Sensitivity<select value={astarFormData.sensitivity} onChange={(e) => setAstarFormData({...astarFormData, sensitivity: e.target.value})}><option>Low</option><option>Medium</option><option>High</option></select></label>
                <label>Time<select value={astarFormData.time} onChange={(e) => setAstarFormData({...astarFormData, time: e.target.value})}><option>Morning</option><option>Afternoon</option><option>Evening</option><option>Night</option></select></label>
              </div>
              <button className="sim-btn" type="button" onClick={runAstarAnalysis} disabled={astarLoading}>{astarLoading ? 'Analyzing...' : 'Run A* Analysis'}</button>
            </article>

            <article className="panel">
              <div className="panel-head"><h2>A* Results</h2></div>
              {astarResult ? (
                <div className="results-card">
                  <div className={`decision ${astarResult.decision?.toLowerCase() || 'review'}`}>
                    {astarResult.decision === 'APPROVE' && <FiCheckCircle />}
                    {astarResult.decision === 'DENY' && <FiXCircle />}
                    {astarResult.decision !== 'APPROVE' && astarResult.decision !== 'DENY' && <FiAlertTriangle />}
                    <strong>{astarResult.decision || 'REVIEW'}</strong>
                  </div>
                  <div className="result-metrics">
                    <div className="metric"><p>Algorithm</p><h4>{astarResult.algorithm}</h4></div>
                  </div>
                  {astarResult.recommendation && <p className="sim-reason">{astarResult.recommendation}</p>}
                </div>
              ) : (
                <p style={{color: '#91a4cc', textAlign: 'center', padding: '40px'}}>Run analysis to see results</p>
              )}
            </article>
          </section>
        </motion.div>
      )}

      {activeTab === 'genetic' && (
        <motion.div
          key="genetic"
          id="panel-genetic"
          role="tabpanel"
          aria-labelledby="tab-genetic"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2><FiGitBranch style={{ marginRight: '8px' }} />Genetic Algorithm Configuration</h2></div>
              <div className="simulator">
                <label className="sim-range-field">
                  Population Size: {gaFormData.popSize}
                  <span className="sim-range-shell">
                    <span className="sim-range-fill" style={{transform: `scaleX(${gaFormData.popSize / 50})`, transformOrigin: 'left center'}} />
                    <input className="sim-range-input" type="range" min="10" max="50" step="1" value={gaFormData.popSize} onChange={(e) => setGaFormData({...gaFormData, popSize: Number(e.target.value)})} />
                  </span>
                </label>
                <label className="sim-range-field">
                  Generations: {gaFormData.generations}
                  <span className="sim-range-shell">
                    <span className="sim-range-fill" style={{transform: `scaleX(${gaFormData.generations / 100})`, transformOrigin: 'left center'}} />
                    <input className="sim-range-input" type="range" min="10" max="100" step="5" value={gaFormData.generations} onChange={(e) => setGaFormData({...gaFormData, generations: Number(e.target.value)})} />
                  </span>
                </label>
                <label className="sim-range-field">
                  Training Sample: {gaFormData.sampleSize}
                  <span className="sim-range-shell">
                    <span className="sim-range-fill" style={{transform: `scaleX(${gaFormData.sampleSize / 1000})`, transformOrigin: 'left center'}} />
                    <input className="sim-range-input" type="range" min="100" max="1000" step="100" value={gaFormData.sampleSize} onChange={(e) => setGaFormData({...gaFormData, sampleSize: Number(e.target.value)})} />
                  </span>
                </label>
              </div>
              <button className="sim-btn" type="button" onClick={runGaOptimization} disabled={gaLoading}>{gaLoading ? 'Evolving Policies...' : 'Run GA Optimization'}</button>
              <small style={{color: '#91a4cc', display: 'block', marginTop: '12px'}}>⏱️ This may take 10-30 seconds to evolve policies</small>
            </article>

            <article className="panel">
              <div className="panel-head"><h2>GA Results</h2></div>
              {gaResult ? (
                <div className="results-card">
                  <div className="result-metrics">
                    <div className="metric"><p>Generations</p><h4>{gaResult.generations_run}</h4></div>
                    <div className="metric"><p>Best Fitness</p><h4>{gaResult.best_policies?.[0]?.fitness_score?.toFixed(4) || 'N/A'}</h4></div>
                    <div className="metric"><p>Improvement</p><h4>{gaResult.improvement?.toFixed(2)}%</h4></div>
                  </div>
                  {gaResult.best_fitness_history && (
                    <ResponsiveContainer width="100%" height={180}>
                      <LineChart data={gaResult.best_fitness_history.map((val, idx) => ({period: idx, fitness: val}))}>
                        <CartesianGrid stroke="#2a3557" strokeDasharray="3 3" />
                        <XAxis dataKey="period" stroke="#91a4cc" />
                        <YAxis stroke="#91a4cc" />
                        <Tooltip />
                        <Line type="monotone" dataKey="fitness" stroke="#9b7bff" />
                      </LineChart>
                    </ResponsiveContainer>
                  )}
                  {gaResult.recommendation && <p className="sim-reason" style={{marginTop: '12px'}}>{gaResult.recommendation}</p>}
                </div>
              ) : (
                <p style={{color: '#91a4cc', textAlign: 'center', padding: '40px'}}>Run optimization to see results</p>
              )}
            </article>
          </section>
        </motion.div>
      )}

      {activeTab === 'hybrid' && (
        <motion.div
          key="hybrid"
          id="panel-hybrid"
          role="tabpanel"
          aria-labelledby="tab-hybrid"
          initial={{ opacity: 0, y: 10 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -6 }}
          transition={{ duration: 0.22, ease: [0.22, 1, 0.36, 1] }}
        >
          <section className="grid">
            <article className="panel">
              <div className="panel-head"><h2><FiZap style={{ marginRight: '8px' }} />Hybrid AI Analysis Setup</h2></div>
              <div className="simulator">
                <label>Role<select value={hybridFormData.role} onChange={(e) => setHybridFormData({...hybridFormData, role: e.target.value})}><option>developer</option><option>admin</option><option>manager</option><option>analyst</option></select></label>
                <label className="sim-range-field">
                  Risk Score: {hybridFormData.risk.toFixed(2)}
                  <span className="sim-range-shell">
                    <span className="sim-range-fill" style={{transform: `scaleX(${Math.max(hybridFormData.risk, 0.025)})`, transformOrigin: 'left center'}} />
                    <input className="sim-range-input" type="range" min="0" max="1" step="0.01" value={hybridFormData.risk} onChange={(e) => setHybridFormData({...hybridFormData, risk: Number(e.target.value)})} />
                  </span>
                </label>
                <label>Failed Attempts<input type="number" min="0" max="10" value={hybridFormData.failed} onChange={(e) => setHybridFormData({...hybridFormData, failed: Number(e.target.value)})} /></label>
                <label>Compliance<select value={hybridFormData.compliance} onChange={(e) => setHybridFormData({...hybridFormData, compliance: e.target.value})}><option>Compliant</option><option>Non-Compliant</option></select></label>
                <label>Sensitivity<select value={hybridFormData.sensitivity} onChange={(e) => setHybridFormData({...hybridFormData, sensitivity: e.target.value})}><option>Low</option><option>Medium</option><option>High</option></select></label>
                <label>Time<select value={hybridFormData.time} onChange={(e) => setHybridFormData({...hybridFormData, time: e.target.value})}><option>Morning</option><option>Afternoon</option><option>Evening</option><option>Night</option></select></label>
              </div>
              <button className="sim-btn" type="button" onClick={runHybridAnalysis} disabled={hybridLoading}>{hybridLoading ? 'Analyzing...' : 'Run Hybrid Analysis'}</button>
              <small style={{color: '#91a4cc', display: 'block', marginTop: '12px'}}>🧠 Combines Prolog, A*, and GA algorithms</small>
            </article>

            <article className="panel">
              <div className="panel-head"><h2>Consensus Decision</h2></div>
              {hybridResult ? (
                <div className="results-card">
                  <div className={`decision ${hybridResult.consensus_decision?.toLowerCase() || 'review'}`}>
                    {hybridResult.consensus_decision === 'APPROVE' && <FiCheckCircle />}
                    {hybridResult.consensus_decision === 'DENY' && <FiXCircle />}
                    {hybridResult.consensus_decision !== 'APPROVE' && hybridResult.consensus_decision !== 'DENY' && <FiAlertTriangle />}
                    <strong>{hybridResult.consensus_decision || 'REVIEW'}</strong>
                  </div>
                  <div className="result-metrics">
                    <div className="metric"><p>Confidence</p><h4>{hybridResult.confidence}</h4></div>
                    <div className="metric"><p>Unanimous</p><h4>{hybridResult.unanimous ? '✅ Yes' : '⚠️ No'}</h4></div>
                  </div>
                  {hybridResult.algorithms && (
                    <div style={{marginTop: '20px', paddingTop: '20px', borderTop: '1px solid #2a3557'}}>
                      <p style={{marginBottom: '12px', color: '#91a4cc'}}><strong>Algorithm Comparison:</strong></p>
                      <div style={{display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '12px'}}>
                        {hybridResult.algorithms.prolog && (
                          <div style={{padding: '12px', background: '#1a2744', borderRadius: '8px', border: '1px solid #2a3557'}}>
                            <p style={{color: '#91a4cc', fontSize: '0.8rem'}}>Prolog</p>
                            <h4 style={{color: '#4fc3f7'}}>{hybridResult.algorithms.prolog.decision}</h4>
                          </div>
                        )}
                        {hybridResult.algorithms.astar && (
                          <div style={{padding: '12px', background: '#1a2744', borderRadius: '8px', border: '1px solid #2a3557'}}>
                            <p style={{color: '#91a4cc', fontSize: '0.8rem'}}>A* Search</p>
                            <h4 style={{color: '#4fc3f7'}}>{hybridResult.algorithms.astar.decision}</h4>
                          </div>
                        )}
                        {hybridResult.algorithms.genetic_algorithm && (
                          <div style={{padding: '12px', background: '#1a2744', borderRadius: '8px', border: '1px solid #2a3557'}}>
                            <p style={{color: '#91a4cc', fontSize: '0.8rem'}}>Genetic Algorithm</p>
                            <h4 style={{color: '#4fc3f7'}}>Optimized</h4>
                          </div>
                        )}
                      </div>
                    </div>
                  )}
                  {hybridResult.recommendation && <p className="sim-reason" style={{marginTop: '12px'}}>{hybridResult.recommendation}</p>}
                </div>
              ) : (
                <p style={{color: '#91a4cc', textAlign: 'center', padding: '40px'}}>Run analysis to see results</p>
              )}
            </article>
          </section>
        </motion.div>
      )}
      </AnimatePresence>
      </main>

      <section className="image-strip">
        {[
          ['https://images.pexels.com/photos/5474298/pexels-photo-5474298.jpeg?auto=compress&cs=tinysrgb&w=800', 'Security analyst reviewing logs'],
          ['https://images.pexels.com/photos/3861969/pexels-photo-3861969.jpeg?auto=compress&cs=tinysrgb&w=800', 'Team monitoring authentication systems'],
          ['https://images.pexels.com/photos/5380642/pexels-photo-5380642.jpeg?auto=compress&cs=tinysrgb&w=800', 'Fingerprint scanner for passwordless access'],
        ].map(([src, alt], idx) => (
          <motion.div
            key={src}
            className="image-strip__slide"
            whileHover={{
              y: -10,
              rotateZ: idx === 1 ? 0 : idx === 0 ? -1.5 : 1.5,
              scale: 1.035,
              transition: { type: 'spring', stiffness: 340, damping: 22 },
            }}
            whileTap={{ scale: 0.995 }}
          >
            <img src={src} alt={alt} />
          </motion.div>
        ))}
      </section>

      <footer className="footer">
        AI project (2026) — Esha Ashfaq &amp; Hadiqa Mehmood
      </footer>
    </div>
    </>
  )
}

export default App
