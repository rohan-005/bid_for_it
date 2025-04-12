<?php

session_start();
require '../login&signup/backend/db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login&signup/login.php");
    exit();
}
$project = [
    'name' => 'BidForIt',
    'duration' => '2 months',
    'mission' => 'To create a seamless online bidding platform that connects buyers and sellers in a transparent, efficient marketplace.',
    'vision' => 'A world where buying and selling through auctions is accessible to everyone, promoting fair pricing and opportunities for all.',
    'values' => [
        'Transparency' => 'We believe in clear, honest transactions with no hidden fees or processes.',
        'Security' => 'We prioritize protecting user data and ensuring safe transactions.',
        'Accessibility' => 'Our platform is designed to be easy to use for everyone.',
        'Innovation' => 'We continually improve our platform with cutting-edge technology.'
    ]
];

// Team members
$team_members = [
    [
        'name' => 'Akansh',
        'position' => 'Full Stack Developer',
        'bio' => 'Akansh contributed to both front-end and back-end development, specializing in user authentication and database management.'
    ],
    [
        'name' => 'Rohan',
        'position' => 'Full Stack Developer',
        'bio' => 'Rohan focused on the bidding system logic and real-time updates, ensuring a smooth bidding experience for users.'
    ],
    [
        'name' => 'Vaishnavi',
        'position' => 'Full Stack Developer',
        'bio' => 'Vaishnavi led the UI/UX design implementation and integration, creating an intuitive and responsive interface.'
    ]
];
?>

<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['theme']) ? $_COOKIE['theme'] : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo htmlspecialchars($project['name']); ?></title>
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

        .mission-vision {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
        }

        .card {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .card-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: var(--spacing-md);
        }

        .value-card {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            padding: var(--spacing-md);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--accent-color);
        }

        .value-card h3 {
            color: var(--primary-color);
            margin-bottom: var(--spacing-xs);
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--spacing-lg);
        }

        .team-member {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            overflow: hidden;
            box-shadow: var(--shadow-md);
            transition: transform 0.3s ease;
        }

        .team-member:hover {
            transform: translateY(-5px);
        }

        .team-member-icon {
            height: 120px;
            width: 100%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--bg-color);
            font-size: 3rem;
        }

        .team-member-info {
            padding: var(--spacing-md);
        }

        .team-member-name {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-xs);
        }

        .team-member-position {
            color: var(--text-light);
            font-size: 0.9rem;
            margin-bottom: var(--spacing-sm);
        }

        .cta-section {
            text-align: center;
            padding: var(--spacing-xl);
            background-color: rgba(var(--primary-color-rgb), 0.05);
            border-radius: var(--radius-lg);
        }

        .cta-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-md);
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

        .stats-section {
            text-align: center;
            margin-bottom: var(--spacing-xl);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--spacing-lg);
        }

        .stat-item {
            padding: var(--spacing-md);
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .stat-number {
            font-size: 2.5rem;
            color: var(--primary-color);
            font-weight: bold;
            margin-bottom: var(--spacing-xs);
        }

        .stat-label {
            color: var(--text-light);
            font-size: 1rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: var(--spacing-lg);
            margin-top: var(--spacing-lg);
        }

        .feature-card {
            background-color: var(--card-bg);
            border-radius: var(--radius-md);
            padding: var(--spacing-lg);
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .feature-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-sm);
        }

        .feature-title {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: var(--spacing-xs);
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
            <h1>About <?php echo htmlspecialchars($project['name']); ?></h1>
            <p>A modern bidding platform built by three passionate full-stack developers in just <?php echo htmlspecialchars($project['duration']); ?>.</p>
        </section>

        <section class="section">
            <h2 class="section-title animate">Our Project</h2>
            <p class="animate delay-1">
                <?php echo htmlspecialchars($project['name']); ?> is an online bidding platform developed as a project by three full-stack developers: Akansh, Rohan, and Vaishnavi. We built this platform from the ground up in just <?php echo htmlspecialchars($project['duration']); ?>, focusing on creating a seamless and intuitive experience for users.
            </p>
            <p class="animate delay-2">
                Our platform enables users to list items for auction, place bids, and track auctions in real-time. We've implemented secure payment processing, user authentication, and a robust notification system to ensure a smooth bidding experience.
            </p>
        </section>

        <section class="section">
            <h2 class="section-title animate">Mission & Vision</h2>
            <div class="mission-vision">
                <div class="card animate delay-1">
                    <h3 class="card-title">Our Mission</h3>
                    <p><?php echo htmlspecialchars($project['mission']); ?></p>
                </div>
                <div class="card animate delay-2">
                    <h3 class="card-title">Our Vision</h3>
                    <p><?php echo htmlspecialchars($project['vision']); ?></p>
                </div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title animate">Our Values</h2>
            <div class="values-grid">
                <?php $delay = 1; ?>
                <?php foreach ($project['values'] as $value => $description): ?>
                    <div class="value-card animate delay-<?php echo $delay; ?>">
                        <h3><?php echo htmlspecialchars($value); ?></h3>
                        <p><?php echo htmlspecialchars($description); ?></p>
                    </div>
                    <?php $delay = ($delay < 4) ? $delay + 1 : 1; ?>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title animate">Key Features</h2>
            <div class="features-grid">
                <div class="feature-card animate delay-1">
                    <div class="feature-icon"><i class="fas fa-gavel"></i></div>
                    <h3 class="feature-title">Real-time Bidding</h3>
                    <p>Place bids instantly and receive live updates on auction status without refreshing the page.</p>
                </div>
                <div class="feature-card animate delay-2">
                    <div class="feature-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3 class="feature-title">Secure Transactions</h3>
                    <p>Encrypted payment processing and secure user authentication to protect your information.</p>
                </div>
                <div class="feature-card animate delay-3">
                    <div class="feature-icon"><i class="fas fa-bell"></i></div>
                    <h3 class="feature-title">Smart Notifications</h3>
                    <p>Stay updated with alerts for outbids, auction endings, and new items matching your interests.</p>
                </div>
                <div class="feature-card animate delay-4">
                    <div class="feature-icon"><i class="fas fa-mobile-alt"></i></div>
                    <h3 class="feature-title">Responsive Design</h3>
                    <p>Bid from any device with our fully responsive interface optimized for mobile, tablet, and desktop.</p>
                </div>
            </div>
        </section>

        <section class="section stats-section">
            <h2 class="section-title animate">Project Stats</h2>
            <div class="stats-grid">
                <div class="stat-item animate delay-1">
                    <div class="stat-number">2</div>
                    <div class="stat-label">Months to Complete</div>
                </div>
                <div class="stat-item animate delay-2">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Team Members</div>
                </div>
                <div class="stat-item animate delay-3">
                    <div class="stat-number">1500+</div>
                    <div class="stat-label">Lines of Code</div>
                </div>
                <div class="stat-item animate delay-4">
                    <div class="stat-number">10+</div>
                    <div class="stat-label">Technologies Used</div>
                </div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title animate">Meet Our Team</h2>
            <div class="team-grid">
                <?php $delay = 1; ?>
                <?php foreach ($team_members as $member): ?>
                    <div class="team-member animate delay-<?php echo $delay; ?>">
                        <div class="team-member-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="team-member-info">
                            <h3 class="team-member-name"><?php echo htmlspecialchars($member['name']); ?></h3>
                            <p class="team-member-position"><?php echo htmlspecialchars($member['position']); ?></p>
                            <p><?php echo htmlspecialchars($member['bio']); ?></p>
                        </div>
                    </div>
                    <?php $delay = ($delay < 3) ? $delay + 1 : 1; ?>
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