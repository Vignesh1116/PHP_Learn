import './App.css';

function App() {
  return (
    <div className="app-container">
      {/* Navigation */}
      <nav className="navbar">
        <div className="logo">LaunchStack</div>
        <div className="nav-links">
          <a href="#features">Features</a>
          <a href="#pricing">Pricing</a>
          <a href="#docs">Documentation</a>
        </div>
        <a href="#features" className="nav-cta">Get Started</a>
      </nav>

      {/* Hero Section */}
      <main className="hero">
        <h1>The Ultimate SaaS Billing & Subscription Engine</h1>
        <p>
          Scale your recurring revenue with confidence. LaunchStack provides the complete backend infrastructure to manage subscriptions, invoicing, and analytics seamlessly.
        </p>
        <div className="hero-actions">
          <a href="#features" className="btn-primary" style={{ display: 'inline-block', textAlign: 'center' }}>Start Building Free</a>
          <a href="#docs" className="btn-secondary" style={{ display: 'inline-block', textAlign: 'center' }}>View Documentation</a>
        </div>
      </main>

      {/* Features Grid */}
      <section id="features" className="features">
        <h2 className="section-title">Built for Modern Startups</h2>
        <div className="grid">
          <div className="card">
            <span className="card-icon">⚡</span>
            <h3>Instant Integration</h3>
            <p>Drop-in APIs and webhooks that connect seamlessly with your existing stack in minutes, not weeks.</p>
          </div>
          <div className="card">
            <span className="card-icon">🔒</span>
            <h3>Secure & Compliant</h3>
            <p>Enterprise-grade security built-in. SOC2 compliant, data encryption at rest, and secure JWT authentication.</p>
          </div>
          <div className="card">
            <span className="card-icon">📊</span>
            <h3>Real-time Analytics</h3>
            <p>Track MRR, churn, and LTV instantly. Make data-driven decisions with our powerful dashboard.</p>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="footer">
        <p>&copy; {new Date().getFullYear()} LaunchStack Inc. All rights reserved.</p>
      </footer>
    </div>
  );
}

export default App;
