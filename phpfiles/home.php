<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BidSphere - Live Auction Platform</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Header Section -->
    <?php include 'header_footer/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero__container container">
            <div class="hero__content">
                <h1 class="hero__title">Rare 1957 Gibson Les Paul</h1>
                <p class="hero__description">One of only 500 ever made, this collector's item is in pristine condition with all original parts.</p>
                
                <div class="hero__stats">
                    <div class="stat__item">
                        <span class="stat__label">Current Bid</span>
                        <span class="stat__value">$150</span>
                    </div>
                    <div class="stat__item">
                        <span class="stat__label">Bids</span>
                        <span class="stat__value">42</span>
                    </div>
                    <div class="stat__item">
                        <span class="stat__label">Time Left</span>
                        <span class="stat__value countdown" data-end="2025-04-20T14:30:00">2d 4h 15m</span>
                    </div>
                </div>
                
                <div class="hero__actions">
                    <a href="auction.php">
                        <button class="btn btn--primary">Place Bid</button>
                    </a>
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
                <h2 class="section__title">Upcoming Auctions <span class="live-badge"><i class="fas fa-circle"></i>Upcoming</span></h2>
                <div class="section__actions">
                    <button class="btn btn--small btn--outline" id="sortPrice">Sort by Price</button>
                    <button class="btn btn--small btn--outline" id="sortTime">Sort by Time</button>
                    <a href="auction.php" class="section__link">Live Auctions</a>
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
    <?php include 'header_footer/footer.php'; ?>


    <script src="../script.js"></script>
</body>
</html>