<?php
session_start();
require '../login&signup/backend/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}

$team_members = [
    [
        'name' => 'Akansh',
        'position' => 'Full Stack Developer',
        'email' => 'akansh@bidhub.com',
        'github' => 'https://github.com/akansh',
        'linkedin' => 'https://linkedin.com/in/akansh',
        'instagram' => 'https://instagram.com/akansh'
    ],
    [
        'name' => 'Rohan',
        'position' => 'Full Stack Developer',
        'email' => 'rohan@bidhub.com',
        'github' => 'https://github.com/rohan',
        'linkedin' => 'https://linkedin.com/in/rohan',
        'instagram' => 'https://instagram.com/rohan'
    ],
    [
        'name' => 'Vaishnavi',
        'position' => 'Full Stack Developer',
        'email' => 'vaishnavi@bidhub.com',
        'github' => 'https://github.com/vaishnavi',
        'linkedin' => 'https://linkedin.com/in/vaishnavi',
        'instagram' => 'https://instagram.com/vaishnavi'
    ]
];

// Process contact form submission
$message_sent = false;
$form_errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    // Simple form validation
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    if (empty($name)) {
        $form_errors['name'] = "Name is required";
    }
    
    if (empty($email)) {
        $form_errors['email'] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors['email'] = "Valid email is required";
    }
    
    if (empty($subject)) {
        $form_errors['subject'] = "Subject is required";
    }
    
    if (empty($message)) {
        $form_errors['message'] = "Message is required";
    }
    
    // If no errors, process the form
    if (empty($form_errors)) {
        // In a real implementation, you would send an email here
        // For this example, we'll just set a flag
        $message_sent = true;
        
        // Clear form data after successful submission
        $name = $email = $subject = $message = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - BidHub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles.css">
    <style>
        .hero {
            margin-top: 10vh;
            text-align: center;
            padding: var(--spacing-xl) var(--spacing-md);
            margin-bottom: var(--spacing-xl);
            background-color: rgba(var(--primary-color-rgb), 0.05);
            border-radius: var(--radius-lg);
        }

        .hero h1 {
            font-size: 3rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-md);
        }

        .hero p {
            font-size: 1.25rem;
            color: var(--text-light);
            max-width: 800px;
            margin: 0 auto;
        }

        .section {
            margin-bottom: var(--spacing-xl);
        }

        .section-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-lg);
            position: relative;
            padding-bottom: var(--spacing-xs);
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background-color: var(--accent-color);
            border-radius: var(--radius-full);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--spacing-lg);
        }

        .contact-info {
            padding: var(--spacing-lg);
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .contact-method {
            margin-bottom: var(--spacing-lg);
            display: flex;
            align-items: flex-start;
        }

        .contact-icon {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-right: var(--spacing-sm);
            min-width: 2rem;
            text-align: center;
        }

        .contact-text h3 {
            font-size: 1.25rem;
            margin-bottom: var(--spacing-xs);
            color: var(--text-color);
        }

        .contact-text p, .contact-text a {
            color: var(--text-light);
            margin-bottom: var(--spacing-xs);
            display: block;
        }

        .social-links {
            display: flex;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-md);
        }

        .social-link {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-full);
            background-color: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            transform: translateY(-3px);
            background-color: var(--secondary-color);
            text-decoration: none;
        }

        .contact-form {
            padding: var(--spacing-lg);
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .form-group {
            margin-bottom: var(--spacing-md);
        }

        .form-label {
            display: block;
            margin-bottom: var(--spacing-xs);
            color: var(--text-color);
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: var(--spacing-sm);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            background-color: var(--bg-color);
            color: var(--text-color);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.2);
        }

        .form-error {
            color: var(--danger-color);
            font-size: 0.875rem;
            margin-top: var(--spacing-xs);
        }

        .btn {
            display: inline-block;
            padding: var(--spacing-sm) var(--spacing-lg);
            background-color: var(--primary-color);
            color: white;
            border-radius: var(--radius-md);
            font-weight: bold;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            text-decoration: none;
        }

        .btn-block {
            width: 100%;
        }

        .alert {
            padding: var(--spacing-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--spacing-md);
        }

        .alert-success {
            background-color: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
        }

        .team-section {
            margin-top: var(--spacing-xl);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
        }

        .team-card {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .team-card:hover {
            transform: translateY(-5px);
        }

        .team-header {
            padding: var(--spacing-md);
            background-color: var(--primary-color);
            color: white;
            text-align: center;
        }

        .team-icon {
            font-size: 3rem;
            margin-bottom: var(--spacing-xs);
        }

        .team-name {
            font-size: 1.5rem;
            margin-bottom: var(--spacing-xs);
        }

        .team-position {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .team-body {
            padding: var(--spacing-md);
        }

        .team-contact {
            margin-bottom: var(--spacing-xs);
            display: flex;
            align-items: center;
        }

        .team-contact i {
            margin-right: var(--spacing-xs);
            color: var(--primary-color);
            width: 20px;
        }

        .team-social {
            display: flex;
            justify-content: center;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-md);
        }

        .map-container {
            height: 400px;
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-top: var(--spacing-lg);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate {
            animation: fadeIn 0.8s ease forwards;
            opacity: 0;
        }

        .delay-1 { animation-delay: 0.2s; }
        .delay-2 { animation-delay: 0.4s; }
        .delay-3 { animation-delay: 0.6s; }
        .delay-4 { animation-delay: 0.8s; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
            }

            .hero h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 480px) {
            .hero h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
<?php include 'header_footer/header.php'; ?>
    

    <div class="container">
        <section class="hero animate">
            <h1>Contact Us</h1>
            <p>Have questions about our bidding platform? Get in touch with our team.</p>
        </section>

        <div class="contact-grid">
            <div class="contact-info animate delay-1">
                <h2 class="section-title">Get In Touch</h2>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Our Location</h3>
                        <p>Tech Innovation Hub</p>
                        <p>123 Developer Avenue</p>
                        <p>Bengaluru, Karnataka 560001</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Email Us</h3>
                        <a href="mailto:contact@bidhub.com">contact@bidhub.com</a>
                        <a href="mailto:support@bidhub.com">support@bidhub.com</a>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Call Us</h3>
                        <p>+91 1234567890</p>
                        <p>Mon-Fri, 9AM-6PM IST</p>
                    </div>
                </div>
                
                <div class="contact-method">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-text">
                        <h3>Working Hours</h3>
                        <p>Monday - Friday: 9AM - 6PM</p>
                        <p>Saturday: 10AM - 2PM</p>
                        <p>Sunday: Closed</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="https://github.com/bidhub" class="social-link" target="_blank" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="https://linkedin.com/company/bidhub" class="social-link" target="_blank" title="LinkedIn">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                    <a href="https://instagram.com/bidhub" class="social-link" target="_blank" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://twitter.com/bidhub" class="social-link" target="_blank" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>

            <div class="contact-form animate delay-2">
                <h2 class="section-title">Send Us a Message</h2>
                
                <?php if ($message_sent): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Thank you for your message! We'll get back to you soon.
                    </div>
                <?php endif; ?>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="form-group">
                        <label for="name" class="form-label">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                        <?php if (isset($form_errors['name'])): ?>
                            <div class="form-error"><?php echo $form_errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Your Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                        <?php if (isset($form_errors['email'])): ?>
                            <div class="form-error"><?php echo $form_errors['email']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" class="form-label">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                        <?php if (isset($form_errors['subject'])): ?>
                            <div class="form-error"><?php echo $form_errors['subject']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="message" class="form-label">Your Message</label>
                        <textarea id="message" name="message" rows="5" class="form-control"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                        <?php if (isset($form_errors['message'])): ?>
                            <div class="form-error"><?php echo $form_errors['message']; ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" name="submit" class="btn btn-block">Send Message</button>
                </form>
            </div>
        </div>

        <section class="section team-section">
            <h2 class="section-title animate">Meet The Team</h2>
            <div class="team-grid">
                <?php foreach ($team_members as $index => $member): ?>
                    <div class="team-card animate delay-<?php echo ($index + 1); ?>">
                        <div class="team-header">
                            <div class="team-icon">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h3 class="team-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="team-position"><?php echo htmlspecialchars($member['position']); ?></p>
                        </div>
                        <div class="team-body">
                            <div class="team-contact">
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:<?php echo htmlspecialchars($member['email']); ?>"><?php echo htmlspecialchars($member['email']); ?></a>
                            </div>
                            <div class="team-social">
                                <a href="<?php echo htmlspecialchars($member['github']); ?>" class="social-link" target="_blank" title="GitHub">
                                    <i class="fab fa-github"></i>
                                </a>
                                <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" class="social-link" target="_blank" title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="<?php echo htmlspecialchars($member['instagram']); ?>" class="social-link" target="_blank" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        
    </div>
    <?php include 'header_footer/footer.php'; ?>
    <script src="../script.js"></script>                
    <script>
        // Animation Observer
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1
            };
            
            const observer = new IntersectionObserver(function(entries, observer) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.animationPlayState = 'running';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            document.querySelectorAll('.animate').forEach(item => {
                item.style.animationPlayState = 'paused';
                observer.observe(item);
            });
        });
    </script>
</body>
</html>