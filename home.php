<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BidSphere - Live Auction Platform</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <header class="header">
        <div class="header__container container">
            <div class="header__logo">
                <a href="home.php" class="logo">
                    <span class="logo__icon"><i class="fas fa-gavel"></i></span>
                    <span class="logo__text">BidSphere</span>
                </a>
            </div>

            <nav class="header__nav">
                <ul class="nav__list">
                    <li class="nav__item"><a href="home.php" class="nav__link ">Home</a></li>
                    <li class="nav__item"><a href="auctions.php" class="nav__link">Auctions</a></li>
                    <li class="nav__item"><a href="profile/profile.php" class="nav__link">Dashboard</a></li>
                    <li class="nav__item"><a href="about.php" class="nav__link">About</a></li>
                </ul>
            </nav>

            <div class="header__actions">
                <button class="action__btn theme-toggle" aria-label="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                
                <div class="action__btn notification" aria-label="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification__badge">3</span>
                    <div class="notification__dropdown">
                        <div class="dropdown__header">
                            <h3>Notifications</h3>
                            <button class="dropdown__close">&times;</button>
                        </div>
                        <ul class="dropdown__list">
                            <li class="dropdown__item unread">
                                <i class="fas fa-exclamation-circle"></i>
                                <div class="item__content">
                                    <p>You've been outbid on "Vintage Rolex Watch"</p>
                                    <small>2 minutes ago</small>
                                </div>
                            </li>
                            <li class="dropdown__item unread">
                                <i class="fas fa-clock"></i>
                                <div class="item__content">
                                    <p>"Signed Basketball" auction ending in 15 minutes</p>
                                    <small>10 minutes ago</small>
                                </div>
                            </li>
                            <li class="dropdown__item">
                                <i class="fas fa-trophy"></i>
                                <div class="item__content">
                                    <p>You won "Rare Comic Book Collection" for $1,250</p>
                                    <small>1 hour ago</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="action__btn user-profile">
                        <div class="profile__avatar">
                            <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                        </div>
                        <span class="profile__name"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <div class="profile__dropdown">
                            <a href="profile/profile.php" class="dropdown__item"><i class="fas fa-user"></i> Profile</a>
                            <a href="my_bids.php" class="dropdown__item"><i class="fas fa-gavel"></i> My Bids</a>
                            <a href="watchlist.php" class="dropdown__item"><i class="fas fa-heart"></i> Watchlist</a>
                            <div class="dropdown__divider"></div>
                            <a href="login&signup/logout.php" class="dropdown__item"><i class="fas fa-sign-out-alt"></i> Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="action__btn user-auth">
                        <a href="login&signup/login.php" class="auth__link">Login</a>
                    </div>
                <?php endif; ?>
                
                <button class="action__btn mobile-menu-toggle" aria-label="Toggle mobile menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__container container">
            <div class="hero__content">
                <h1 class="hero__title">Rare 1957 Gibson Les Paul</h1>
                <p class="hero__description">One of only 500 ever made, this collector's item is in pristine condition with all original parts.</p>
                
                <div class="hero__stats">
                    <div class="stat__item">
                        <span class="stat__label">Current Bid</span>
                        <span class="stat__value">$24,750</span>
                    </div>
                    <div class="stat__item">
                        <span class="stat__label">Bids</span>
                        <span class="stat__value">42</span>
                    </div>
                    <div class="stat__item">
                        <span class="stat__label">Time Left</span>
                        <span class="stat__value countdown" data-end="2025-04-08T14:30:00">2d 4h 15m</span>
                    </div>
                </div>
                
                <div class="hero__actions">
                    <button class="btn btn--primary">Place Bid</button>
                    <button class="btn btn--outline">Watch Auction</button>
                </div>
            </div>
            
            <div class="hero__image">
                <img src="https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="1957 Gibson Les Paul Guitar">
                <div class="image__badge">FEATURED</div>
            </div>
        </div>
    </section>

    <!-- Enhanced Search Section -->
    <section class="search">
        <div class="search__container container">
            <form class="search__form" id="searchForm">
                <div class="form__group search__group">
                    <input type="text" placeholder="Search auctions..." class="form__input" id="searchInput" autocomplete="off">
                    <button type="submit" class="form__submit"><i class="fas fa-search"></i></button>
                    <div class="search__suggestions" id="searchSuggestions"></div>
                </div>
                
                <div class="form__filters">
                    <select class="filter__select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <option value="collectibles">Collectibles</option>
                        <option value="electronics">Electronics</option>
                        <option value="art">Art</option>
                        <option value="jewelry">Jewelry</option>
                        <option value="sports">Sports</option>
                    </select>
                    
                    <select class="filter__select" id="priceFilter">
                        <option value="">Price Range</option>
                        <option value="0-100">$0 - $100</option>
                        <option value="100-500">$100 - $500</option>
                        <option value="500-1000">$500 - $1,000</option>
                        <option value="1000-5000">$1,000 - $5,000</option>
                        <option value="5000+">$5,000+</option>
                    </select>
                    
                    <select class="filter__select" id="timeFilter">
                        <option value="">Ending Time</option>
                        <option value="1h">Ending in 1 hour</option>
                        <option value="6h">Ending in 6 hours</option>
                        <option value="24h">Ending in 24 hours</option>
                        <option value="3d">Ending in 3 days</option>
                    </select>
                    
                    <button type="button" class="btn btn--outline" id="resetFilters">Reset</button>
                </div>
            </form>
        </div>
    </section>

    <!-- Enhanced Live Auctions Section -->
    <section class="section live-auctions">
        <div class="section__container container">
            <div class="section__header">
                <h2 class="section__title">Live Auctions <span class="live-badge"><i class="fas fa-circle"></i> LIVE</span></h2>
                <div class="section__actions">
                    <button class="btn btn--small btn--outline" id="sortPrice">Sort by Price</button>
                    <button class="btn btn--small btn--outline" id="sortTime">Sort by Time</button>
                    <a href="auctions.php" class="section__link">View All</a>
                </div>
            </div>
            
            <div class="auctions__grid" id="auctionsGrid">
                <!-- Auction cards will be populated by JavaScript -->
            </div>
            
            <div class="live-updates">
                <div class="update__item">
                    <span class="update__icon"><i class="fas fa-bolt"></i></span>
                    <span class="update__text">User42 just bid $1,350 on Vintage Rolex Watch</span>
                </div>
                <div class="update__item">
                    <span class="update__icon"><i class="fas fa-gavel"></i></span>
                    <span class="update__text">Auction for Signed Baseball ending in 15 minutes!</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Leaderboard Section -->
    <section class="section leaderboard">
        <div class="section__container container">
            <div class="section__header">
                <h2 class="section__title">Today's Top Bids</h2>
            </div>
            
            <div class="leaderboard__table-container">
                <table class="leaderboard__table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>User</th>
                            <th>Time</th>
                            <th>Bid Amount</th>
                            <th>Multiplier</th>
                            <th>Final Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Painting</td>
                            <td>User71</td>
                            <td>7:49:25 PM</td>
                            <td>$344</td>
                            <td>2.76x</td>
                            <td class="positive">$3189</td>
                        </tr>
                        <tr>
                            <td>Rare Coin</td>
                            <td>User61</td>
                            <td>7:49:25 PM</td>
                            <td>$986</td>
                            <td>4.22x</td>
                            <td class="positive">$3281</td>
                        </tr>
                        <tr>
                            <td>Collector's Watch</td>
                            <td>User245</td>
                            <td>7:49:25 PM</td>
                            <td>$117</td>
                            <td>1.46x</td>
                            <td class="positive">$3414</td>
                        </tr>
                        <tr>
                            <td>Vintage Clock</td>
                            <td>User388</td>
                            <td>7:49:25 PM</td>
                            <td>$290</td>
                            <td>4.93x</td>
                            <td class="negative">-$1802</td>
                        </tr>
                        <tr>
                            <td>Jewelry Set</td>
                            <td>User392</td>
                            <td>7:49:25 PM</td>
                            <td>$451</td>
                            <td>4.59x</td>
                            <td class="negative">-$5010</td>
                        </tr>
                        <tr>
                            <td>Vintage Clock</td>
                            <td>User519</td>
                            <td>7:49:25 PM</td>
                            <td>$661</td>
                            <td>0.15x</td>
                            <td class="positive">$1695</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <footer class="footer">
        <div class="footer__container container">
            <div class="footer__grid">
                <div class="footer__col">
                    <h3 class="footer__title">BidSphere</h3>
                    <p class="footer__text">The premier platform for live auctions of rare and collectible items.</p>
                    <div class="footer__social">
                        <a href="#" class="social__link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social__link"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                
                <div class="footer__col">
                    <h3 class="footer__title">Quick Links</h3>
                    <ul class="footer__list">
                        <li><a href="home.php" class="footer__link">Home</a></li>
                        <li><a href="auctions.php" class="footer__link">Auctions</a></li>
                        <li><a href="about.php" class="footer__link">About Us</a></li>
                        <li><a href="contact.php" class="footer__link">Contact</a></li>
                    </ul>
                </div>
                
               
                
                <div class="footer__col">
                    <h3 class="footer__title">Contact Us</h3>
                    <ul class="footer__list">
                        <li><i class="fas fa-map-marker-alt"></i>Lovely Professional University</li>
                        <li><i class="fas fa-phone"></i>+91 7894561237</li>
                        <li><i class="fas fa-envelope"></i> greeenxd@bidshpere.com</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer__bottom">
                <p class="footer__copyright">&copy; 2025 greeenxd. All rights reserved.</p>
                <div class="footer__links">
                    <a href="#" class="footer__link">Privacy Policy</a>
                    <a href="#" class="footer__link">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>