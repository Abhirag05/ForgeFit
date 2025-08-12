<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ForgeFit</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="icon" href="assets/images/logo.png" type="image/x-icon">
  <style>
    :root {
      --primary: #ff4d4d;
      --secondary: #6a00ff;
      --dark: #0a0a12;
      --light: #f0f0f0;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }

    body {
      background-color: var(--dark);
      color: var(--light);
      overflow-x: hidden;
    }

    /* Lightning Particle Background */
    #particles-js {
      position: fixed;
      width: 100%;
      height: 100%;
      z-index: -1;
      background: linear-gradient(135deg, #000000, #0b0016, #0f0c29);
    }

    /* Simplified Navbar */
    nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.5rem 5%;
      position: fixed;
      width: 100%;
      z-index: 1000;
      flex-wrap: wrap;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 700;
      background:white;
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      letter-spacing: 1px;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      flex-wrap: wrap;
      transition: gap 0.3s, font-size 0.3s;
      list-style: none;
      padding-left: 0;
    }
    .nav-links li {
      list-style: none;
    }

    .nav-links a {
      color: rgb(190, 189, 189);
      text-decoration: none;
      font-weight: 500;
      position: relative;
      opacity: 0.9;
      transition: all 0.3s;
    }

          /* Initial Navbar State */
        nav {
          background: transparent;
          transition: background 0.3s ease;
        }

        /* Navbar on Scroll */
        nav.scrolled {
          background: rgba(10, 10, 18, 0.7);
          backdrop-filter: blur(10px);
        }

        /* Modern Hover Effect for Navbar Links */
        .nav-links a {
          position: relative;
          overflow: hidden;
        }

        .nav-links a::before {
          content: '';
          position: absolute;
          bottom: 0;
          left: 0;
          width: 100%;
          height: 2px;
          background: white;
          transform: scaleX(0);
          transition: transform 0.3s ease;
        }
        .nav-links a:hover{
          color: white;
        }
        .nav-links a:hover::before {
          transform: scaleX(1);
        }

        /* Active Section Highlight */
        .nav-links a.active {
          color:red;
         transform: scaleX(1);
        }

    .signin-btn {
      background: transparent;
      color: var(--primary);
      border: 2px solid var(--primary);
      padding: 0.6rem 1.5rem;
      border-radius: 30px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s;
    }
    .signin-btn a{
      text-decoration: none;
      color:white;
    }
    .signin-btn:hover {
      background: var(--primary);
      color: white;
      box-shadow: 0 0 15px var(--primary);
    }

    /* Hero Section */
    .hero {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      padding: 0 5%;
      position: relative;
    }

    .hero h1 {
      font-size: 4rem;
      margin-bottom: 1.5rem;
      background: linear-gradient(90deg, var(--primary), var(--light), var(--primary));
      background-size: 200% auto;
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      animation: gradientFlow 5s linear infinite;
    }

    .hero p {
      font-size: 1.2rem;
      max-width: 700px;
      margin-bottom: 2.5rem;
      opacity: 0.9;
      line-height: 1.6;
    }

    @keyframes gradientFlow {
      0% { background-position: 0% center; }
      100% { background-position: 200% center; }
    }

    .cta-btn {
      background: linear-gradient(45deg, var(--primary), var(--secondary));
      color: white;
      border: none;
      padding: 1rem 2.5rem;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 50px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      z-index: 1;
      transition: all 0.3s;
      box-shadow: 0 10px 20px rgba(255, 77, 77, 0.3);
    }
    .cta-btn a{
      text-decoration: none;
      color: white;
    }

    .cta-btn:hover {
      transform: translateY(-3px);
      box-shadow: 0 15px 30px rgba(255, 77, 77, 0.4);
    }

    /* Features Section */
    .features {
      padding: 6rem 5%;
      position: relative;
      background: rgba(10, 10, 18, 0.7);
    }

    .features h2 {
      text-align: center;
      font-size: 3rem;
      margin-bottom: 3rem;
      position: relative;
      color: var(--primary);
    }

    .features h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: var(--primary);
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 2rem;
      position: relative;
      z-index: 1;
    }

    .feature-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border-radius: 15px;
      padding: 2rem;
      transition: all 0.3s;
      border: 1px solid rgba(255, 77, 77, 0.1);
    }

    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(255, 77, 77, 0.2);
      border-color: var(--primary);
    }

    .feature-card i {
      font-size: 2.5rem;
      margin-bottom: 1.5rem;
      color: var(--primary);
    }

    .feature-card h3 {
      font-size: 1.5rem;
      margin-bottom: 1rem;
      color: var(--accent);
    }

    .feature-card p {
      opacity: 0.8;
      line-height: 1.6;
    }

    /* FAQ Section */
    .faq {
      padding: 6rem 5%;
      background: rgba(10, 10, 18, 0.7);
    }

    .faq h2 {
      text-align: center;
      font-size: 3rem;
      margin-bottom: 3rem;
      color: var(--primary);
    }

    .accordion {
      max-width: 800px;
      margin: 0 auto;
    }

    .accordion-item {
      margin-bottom: 1rem;
      border-radius: 10px;
      overflow: hidden;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 77, 77, 0.1);
    }

    .accordion-header {
      padding: 1.5rem;
      cursor: pointer;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-weight: 600;
      transition: all 0.3s;
    }

    .accordion-header:hover {
      background: rgba(255, 77, 77, 0.1);
    }

    .accordion-header i {
      transition: transform 0.3s;
    }

    .accordion-content {
      max-height: 0;
      overflow: hidden;
      transition: max-height 0.3s ease-out;
      padding: 0 1.5rem;
      background: rgba(10, 10, 18, 0.5);
    }

    .accordion-item.active .accordion-header {
      background: rgba(255, 77, 77, 0.1);
    }

    .accordion-item.active .accordion-header i {
      transform: rotate(180deg);
    }

    .accordion-item.active .accordion-content {
      max-height: 300px;
      padding: 1.5rem;
    }

    /* CTA Section */
    .cta-section {
      padding: 6rem 5%;
      text-align: center;
      position: relative;
    }

    .cta-section h2 {
      font-size: 3rem;
      margin-bottom: 1.5rem;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .cta-section p {
      max-width: 700px;
      margin: 0 auto 2.5rem;
      opacity: 0.9;
      line-height: 1.6;
    }

    /* Contact Section */
    .contact {
      padding: 6rem 5%;
      position: relative;
      background: rgba(10, 10, 18, 0.7);
    }

    .contact h2 {
      text-align: center;
      font-size: 3rem;
      margin-bottom: 3rem;
      position: relative;
      color: var(--primary);
    }

    .contact h2::after {
      content: '';
      position: absolute;
      bottom: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 100px;
      height: 3px;
      background: var(--primary);
    }

    .contact-form {
      max-width: 600px;
      margin: 0 auto;
      position: relative;
      z-index: 1;
    }

    .form-group {
      margin-bottom: 1.5rem;
      position: relative;
    }

    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 1.2rem;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 77, 77, 0.3);
      border-radius: 10px;
      color: white;
      font-size: 1rem;
      transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 10px rgba(255, 77, 77, 0.3);
    }

    .form-group label {
      position: absolute;
      top: 1.2rem;
      left: 1.2rem;
      color: rgba(255, 255, 255, 0.7);
      transition: all 0.3s;
      pointer-events: none;
    }

    .form-group input:focus + label,
    .form-group textarea:focus + label,
    .form-group input:not(:placeholder-shown) + label,
    .form-group textarea:not(:placeholder-shown) + label {
      top: -0.8rem;
      left: 1rem;
      font-size: 0.8rem;
      background: var(--dark);
      padding: 0 0.5rem;
      color: var(--primary);
    }

    /* Footer */
    footer {
      background: rgba(10, 10, 18, 0.9);
      padding: 4rem 5% 2rem;
      border-top: 1px solid rgba(255, 77, 77, 0.1);
    }

    .footer-content {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 2rem;
      margin-bottom: 2rem;
    }

    .footer-column h3 {
      font-size: 1.2rem;
      margin-bottom: 1.5rem;
      color: var(--primary);
    }

    .footer-column ul {
      list-style: none;
    }

    .footer-column ul li {
      margin-bottom: 0.8rem;
    }

    .footer-column ul li a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: all 0.3s;
    }

    .footer-column ul li a:hover {
      color: var(--primary);
      padding-left: 5px;
    }

    .social-links {
      display: flex;
      gap: 1rem;
    }

    .social-links a {
      color: white;
      background: rgba(255, 255, 255, 0.1);
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      justify-content: center;
      align-items: center;
      transition: all 0.3s;
      text-decoration: none;
      margin-top: 10px;
    }

    .social-links a:hover {
      background: var(--primary);
      transform: translateY(-3px);
    }

    .footer-bottom {
      text-align: center;
      padding-top: 2rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      color: rgba(255, 255, 255, 0.5);
      font-size: 0.9rem;
    }

    /* Responsive Adjustments */
    @media (max-width: 1200px) {
      .hero h1 {
        font-size: 3.5rem;
      }
      .hero p {
        font-size: 1.1rem;
      }
    }

    @media (max-width: 900px) {
      nav {
        padding: 1rem 2%;
      }
      .logo {
        font-size: 1.2rem;
      }
      .nav-links {
        gap: 1rem;
      }
      .nav-links a {
        font-size: 0.95rem;
        padding: 0.3rem 0.3rem;
      }
      .signin-btn {
        padding: 0.4rem 1rem;
        font-size: 0.95rem;
      }
      
      .hero h1 {
        font-size: 3rem;
      }
      .hero p {
        font-size: 1rem;
        max-width: 600px;
      }
      .cta-btn {
        padding: 0.9rem 2rem;
        font-size: 1rem;
      }
    }

    @media (max-width: 768px) {
      .hero h1 {
        font-size: 2.5rem;
      }
      .hero p {
        font-size: 0.95rem;
        max-width: 500px;
      }
    }

    @media (max-width: 600px) {
      nav {
        flex-direction: column;
        align-items: stretch;
        padding: 0.5rem 1%;
      }
      .logo {
        font-size: 1rem;
        color: #fff;
        background: none;
        -webkit-background-clip: initial;
        background-clip: initial;
        font-weight: 900;
        letter-spacing: 2px;
        text-shadow: 0 1px 4px rgba(0,0,0,0.18);
      }
      .nav-links {
        gap: 0.5rem;
        justify-content: center;
      }
      .nav-links a {
        font-size: 0.85rem;
        padding: 0.2rem 0.2rem;
      }
      .signin-btn {
        padding: 0.3rem 0.7rem;
        font-size: 0.85rem;
        display: none;
      }
      
      .hero h1 {
        font-size: 2rem;
        margin-bottom: 1rem;
      }
      .hero p {
        font-size: 0.9rem;
        max-width: 90%;
        margin-bottom: 1.5rem;
      }
      .cta-btn {
        padding: 0.8rem 1.8rem;
        font-size: 0.95rem;
      }
    }

    @media (max-width: 400px) {
      .hero h1 {
        font-size: 1.8rem;
      }
      .hero p {
        font-size: 0.85rem;
      }
    }
  </style>
</head>
<body>
  <!-- Lightning Particle Background -->
  <div id="particles-js"></div>

  <!-- Simplified Navbar -->
  <nav>
    <div class="logo">ForgeFit</div>
    <ul class="nav-links">
      <li><a href="#">Home</a></li>
      <li><a href="#features">Features</a></li>
      <li><a href="#faq">FAQ</a></li>
      <li><a href="#contact">Contact</a></li>
    </ul>
    <a href="signin.php"><button class="signin-btn">Sign In</button></a>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <h1>Transform Your Fitness Journey</h1>
    <p>Track workouts, meals, and progress with our gamified fitness platform. Level up your health like never before.</p>
   <a href="signin.php"> <button class="cta-btn">Get Started</button></a>
  </section>

  <!-- Features Section -->
  <section class="features" id="features">
    <h2>Key Features</h2>
    <div class="features-grid">
      <div class="feature-card">
        <i class="fas fa-dumbbell"></i>
        <h3>Workout Tracking</h3>
        <p>Log every exercise with detailed analytics to monitor your strength and endurance progress.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-utensils"></i>
        <h3>Nutrition Logging</h3>
        <p>Track meals and macros with our comprehensive food database and barcode scanner.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-chart-line"></i>
        <h3>Progress Analytics</h3>
        <p>Visualize your fitness journey with interactive charts and personalized insights.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-medal"></i>
        <h3>Achievement System</h3>
        <p>Earn badges and level up as you hit milestones in your fitness journey.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-users"></i>
        <h3>Community Challenges</h3>
        <p>Join group challenges to stay motivated and compete with friends.</p>
      </div>
      <div class="feature-card">
        <i class="fas fa-calendar-check"></i>
        <h3>Habit Builder</h3>
        <p>Create and track daily habits to build lasting lifestyle changes.</p>
      </div>
    </div>
  </section>

  <!-- FAQ Section -->
  <section class="faq" id="faq">
    <h2>Frequently Asked Questions</h2>
    <div class="accordion">
      <div class="accordion-item">
        <div class="accordion-header">
          <span>How does the leveling system work?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="accordion-content">
          <p>You earn points for completing workouts, logging meals, and maintaining streaks. As you accumulate points, you'll level up and unlock new features.</p>
        </div>
      </div>
      <div class="accordion-item">
        <div class="accordion-header">
          <span>Is ForgeFit free to use?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="accordion-content">
          <p>Yes! The basic version is completely free. Premium members get access to advanced analytics and exclusive challenges.</p>
        </div>
      </div>
      <div class="accordion-item">
        <div class="accordion-header">
          <span>Can I sync with other fitness apps?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="accordion-content">
          <p>We integrate with Apple Health, Google Fit, and most wearable devices.</p>
        </div>
      </div>
      <div class="accordion-item">
        <div class="accordion-header">
          <span>How do challenges work?</span>
          <i class="fas fa-chevron-down"></i>
        </div>
        <div class="accordion-content">
          <p>Challenges are time-based goals (usually 7-30 days) where you compete with others to complete fitness objectives for rewards.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
    <section class="cta-section">
    <h2>Ready to Transform Your Fitness?</h2>
    <p>Join thousands of users who've leveled up their health with ForgeFit.</p>
    <button class="cta-btn"><a href="signin.html">Start Your Free Trial</a></button>
  </section>

  <!-- Contact Section -->
  <section class="contact" id="contact">
    <h2>Contact Us</h2>
    <form class="contact-form">
      <div class="form-group">
        <input type="text" id="name" placeholder=" ">
        <label for="name">Your Name</label>
      </div>
      <div class="form-group">
        <input type="email" id="email" placeholder=" ">
        <label for="email">Email Address</label>
      </div>
      <div class="form-group">
        <textarea id="message" placeholder=" " rows="5"></textarea>
        <label for="message">Your Message</label>
      </div>
      <button type="submit" class="cta-btn">Send Message</button>
    </form>
  </section>

  <!-- Footer -->
  <footer>
    <div class="footer-content">
      <div class="footer-column">
        <h3>ForgeFit</h3>
        <p>The ultimate fitness tracking platform with gamified motivation.</p>
        <div class="social-links">
          <a href="#"><i class="fab fa-facebook-f"></i></a>
          <a href="#"><i class="fab fa-twitter"></i></a>
          <a href="#"><i class="fab fa-instagram"></i></a>
        </div>
      </div>
      <div class="footer-column">
        <h3>Quick Links</h3>
        <ul>
          <li><a href="#">Home</a></li>
          <li><a href="#features">Features</a></li>
          <li><a href="#faq">FAQ</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h3>Legal</h3>
        <ul>
          <li><a href="#">Terms</a></li>
          <li><a href="#">Privacy</a></li>
          <li><a href="#">Cookies</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 ForgeFit. All rights reserved.</p>
    </div>
  </footer>

  <!-- Scripts -->
   <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
  <script>
    // Particle.js config (Solo Leveling mana effect)
    particlesJS("particles-js", {
      "particles": {
        "number": { "value": 80, "density": { "enable": true, "value_area": 800 } },
        "color": { "value": "#ff4d4d" },
        "shape": { "type": "circle" },
        "opacity": { "value": 0.5, "random": true },
        "size": { "value": 3, "random": true },
        "line_linked": { "enable": true, "distance": 150, "color": "#6a00ff", "opacity": 0.2, "width": 1 },
        "move": { "enable": true, "speed": 2, "direction": "none", "random": true, "straight": false, "out_mode": "out" }
      },
      "interactivity": {
        "detect_on": "canvas",
        "events": {
          "onhover": { "enable": true, "mode": "repulse" },
          "onclick": { "enable": true, "mode": "push" }
        }
      }
    });

    // Accordion FAQ
    const accordionItems = document.querySelectorAll('.accordion-item');
    accordionItems.forEach(item => {
      const header = item.querySelector('.accordion-header');
      header.addEventListener('click', () => {
        item.classList.toggle('active');
      });
    });

    // Floating label effect
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach(group => {
      const input = group.querySelector('input, textarea');
      input.addEventListener('focus', () => {
        group.querySelector('label').style.color = 'var(--primary)';
      });
      input.addEventListener('blur', () => {
        if (!input.value) {
          group.querySelector('label').style.color = '';
        }
      });
    });
    // Add scrolled class to navbar on scroll
window.addEventListener('scroll', () => {
  const nav = document.querySelector('nav');
  if (window.scrollY > 50) {
    nav.classList.add('scrolled');
  } else {
    nav.classList.remove('scrolled');
  }
});

// Highlight active section in navbar
const sections = document.querySelectorAll('section');
const navLinks = document.querySelectorAll('.nav-links a');

function highlightActiveSection() {
  let current = '';
  sections.forEach(section => {
    const sectionTop = section.offsetTop;
    const sectionHeight = section.clientHeight;
    if (pageYOffset >= sectionTop - sectionHeight / 3) {
      current = section.getAttribute('id');
    }
  });

  navLinks.forEach(link => {
    link.classList.remove('active');
    if (link.href.includes(`#${current}`)) {
      link.classList.add('active');
    }
  });
}

window.addEventListener('scroll', highlightActiveSection);
  </script>
</body>
</html>