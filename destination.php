<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" href="assets/images/main-logo.png">
    <link rel="stylesheet" href="assets/vendors/bootstrap/css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="assets/vendors/fontawesome/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/jquery-ui/jquery-ui.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/modal-video/modal-video.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/lightbox/dist/css/lightbox.min.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/slick/slick.css">
    <link rel="stylesheet" type="text/css" href="assets/vendors/slick/slick-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,400&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css">

    <title>SRI SOUL VENTURES</title>

    <style>
        :root {
            --primary-color: #1f6b57;
            --secondary-color: #f4a261;
            --text-dark: #222;
            --text-light: #666;
            --bg-light: #f8fafb;
            --white: #ffffff;
            --border-light: #e9eef1;
            --shadow-soft: 0 10px 30px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 18px 40px rgba(0, 0, 0, 0.12);
            --radius-lg: 18px;
            --radius-md: 12px;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-light);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Raleway', sans-serif;
        }

        .inner-banner-wrap {
            margin-bottom: 40px;
        }

        .inner-baner-container {
            position: relative;
            background-size: cover;
            background-position: center center;
            padding: 140px 0 110px;
        }

        .inner-baner-container::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(0,0,0,0.65), rgba(0,0,0,0.35));
        }

        .inner-banner-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .inner-title {
            color: #fff;
            font-size: 52px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .destinations-wrapper {
            padding: 10px 0 70px;
        }

        .section-intro {
            text-align: center;
            max-width: 760px;
            margin: 0 auto 40px;
        }

        .section-intro h2 {
            font-size: 36px;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .section-intro p {
            font-size: 16px;
            color: var(--text-light);
            margin: 0;
        }

        .destination-card {
            background: var(--white);
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-soft);
            margin-bottom: 35px;
            transition: all 0.35s ease;
            border: 1px solid var(--border-light);
        }

        .destination-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .destination-card .row {
            align-items: stretch;
        }

        .destination-image-wrap {
            height: 100%;
            min-height: 340px;
            position: relative;
            overflow: hidden;
        }

        .destination-image-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            transition: transform 0.5s ease;
        }

        .destination-card:hover .destination-image-wrap img {
            transform: scale(1.05);
        }

        .destination-content {
            padding: 35px 32px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            height: 100%;
        }

        .destination-content h2 {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 18px;
            color: var(--primary-color);
            position: relative;
        }

        .destination-content h2::after {
            content: "";
            display: block;
            width: 65px;
            height: 4px;
            background: var(--secondary-color);
            border-radius: 20px;
            margin-top: 12px;
        }

        .vibe-badge {
            display: inline-block;
            background: rgba(31, 107, 87, 0.08);
            color: var(--primary-color);
            font-weight: 700;
            font-size: 14px;
            padding: 8px 14px;
            border-radius: 30px;
            margin-bottom: 20px;
        }

        .info-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 12px;
            color: var(--text-dark);
        }

        .destination-content ul {
            padding-left: 0;
            margin-bottom: 20px;
            list-style: none;
        }

        .destination-content ul li {
            position: relative;
            padding-left: 28px;
            margin-bottom: 10px;
            color: var(--text-light);
            line-height: 1.7;
        }

        .destination-content ul li::before {
            content: "\f058";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            top: 2px;
            color: var(--secondary-color);
        }

        .destination-note {
            background: #f9fbfc;
            border-left: 4px solid var(--primary-color);
            padding: 16px 18px;
            border-radius: 10px;
            margin-top: 8px;
        }

        .destination-note p {
            margin: 0 0 8px;
            color: var(--text-dark);
            line-height: 1.7;
        }

        .destination-note p:last-child {
            margin-bottom: 0;
        }

        .destination-note strong {
            color: var(--primary-color);
        }

        .destination-card.reverse .row {
            flex-direction: row-reverse;
        }

        @media (max-width: 991px) {
            .inner-title {
                font-size: 40px;
            }

            .destination-image-wrap {
                min-height: 280px;
            }

            .destination-content {
                padding: 28px 24px;
            }

            .destination-content h2 {
                font-size: 26px;
            }
        }

        @media (max-width: 767px) {
            .inner-baner-container {
                padding: 100px 0 80px;
            }

            .inner-title {
                font-size: 32px;
            }

            .section-intro h2 {
                font-size: 28px;
            }

            .destination-card.reverse .row,
            .destination-card .row {
                flex-direction: column;
            }

            .destination-image-wrap {
                min-height: 240px;
            }

            .destination-content {
                padding: 24px 20px;
            }

            .destination-content h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body class="home">
    <div id="siteLoader" class="site-loader">
        <div class="preloader-content">
            <img src="assets/images/loader1.gif" alt="">
        </div>
    </div>

    <div id="page" class="full-page">
        <?php include('nav.php'); ?>

        <main id="content" class="site-main">
            <section class="inner-banner-wrap">
                <div class="inner-baner-container" style="background-image: url(https://thriftynomads.com/wp-content/uploads/2018/07/Nine-Arches-Bridge.jpg);">
                    <div class="container">
                        <div class="inner-banner-content">
                            <h1 class="inner-title">Destinations</h1>
                        </div>
                    </div>
                </div>
                <div class="inner-shape"></div>
            </section>

            <section class="destinations-wrapper">
                <div class="container">
                    <div class="section-intro">
                        <h2>Explore Sri Lanka by Region</h2>
                        <p>From sun-kissed beaches to misty mountains and timeless heritage, discover the unique spirit of each destination.</p>
                    </div>

                    <!-- 1 -->
                    <div class="destination-card">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/22.jpg" alt="Down South Coast">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Down South Coast</h2>
                                    <span class="vibe-badge">Vibe: Relaxed, Coastal, Trendy</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Surfing (Weligama, Hiriketiya)</li>
                                        <li>Whale watching (Mirissa)</li>
                                        <li>Colonial sites (Galle Fort)</li>
                                        <li>Beach clubs, yoga, and local food scenes</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Couples, backpackers, digital nomads, surfers</p>
                                        <p><strong>Travel Note:</strong> Hot and sunny, best from Nov–Apr</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2 -->
                    <div class="destination-card reverse">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/23.jpg" alt="Hill Country">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Hill Country</h2>
                                    <span class="vibe-badge">Vibe: Cool, Scenic, Peaceful</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Tea plantations (Nuwara Eliya, Haputale)</li>
                                        <li>Scenic train rides (Ella to Kandy)</li>
                                        <li>Waterfalls, hiking trails, Nine Arches Bridge</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Nature lovers, photographers, slow travelers</p>
                                        <p><strong>Travel Note:</strong> Cool year-round, often misty and rainy</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3 -->
                    <div class="destination-card">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/24.jpg" alt="Cultural Triangle">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Cultural Triangle</h2>
                                    <span class="vibe-badge">Vibe: Spiritual, Historic, Sacred</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Sigiriya Rock Fortress</li>
                                        <li>Dambulla Cave Temple</li>
                                        <li>Ancient capitals (Anuradhapura &amp; Polonnaruwa)</li>
                                        <li>Temple of the Sacred Tooth (Kandy)</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Culture seekers, pilgrims, history buffs</p>
                                        <p><strong>Travel Note:</strong> Dry zone, best from May–September</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 4 -->
                    <div class="destination-card reverse">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/25.jpg" alt="Wildlife & National Parks">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Wildlife &amp; National Parks</h2>
                                    <span class="vibe-badge">Vibe: Wild, Natural, Untamed</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Leopards in Yala</li>
                                        <li>Elephants in Udawalawa &amp; Minneriya</li>
                                        <li>Birds in Bundala &amp; Kumana</li>
                                        <li>Camping and safaris</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong>Adventure travelers, wildlife photographers, families</p>
                                        <p><strong>Travel Note:</strong> Best time varies by park; dry seasons ideal</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 5 -->
                    <div class="destination-card">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/26.jpg" alt="East Coast">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>East Coast</h2>
                                    <span class="vibe-badge">Vibe: Laid-back, Vibrant, Authentic</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Surfing (Arugam Bay)</li>
                                        <li>Snorkeling &amp; diving (Trincomalee, Pigeon Island)</li>
                                        <li>Hindu temples and local festivals</li>
                                        <li>Quiet beaches and untouched coastlines</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Offbeat explorers, surfers, culture lovers</p>
                                        <p><strong>Travel Note:</strong> Best visited between April–October</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 6 -->
                    <div class="destination-card reverse">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/27.jpg" alt="Western Coast & Capital Region">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Western Coast &amp; Capital Region</h2>
                                    <span class="vibe-badge">Vibe: Urban, Stylish, Energetic</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Colombo city tours &amp; nightlife</li>
                                        <li>Casinos and luxury hotels</li>
                                        <li>Negombo lagoon tours</li>
                                        <li>Shopping, art galleries, food tours</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Offbeat explorers, surfers, culture lovers</p>
                                        <p><strong>Travel Note:</strong> Best visited between April–October</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 7 -->
                    <div class="destination-card">
                        <div class="row no-gutters">
                            <div class="col-lg-5">
                                <div class="destination-image-wrap">
                                    <img src="assets/destinations/28.jpg" alt="Northern Peninsula">
                                </div>
                            </div>
                            <div class="col-lg-7">
                                <div class="destination-content">
                                    <h2>Northern Peninsula</h2>
                                    <span class="vibe-badge">Vibe: Remote, Spiritual, Cultural Revival</span>

                                    <div class="info-title">Popular for</div>
                                    <ul>
                                        <li>Hindu temples (Nallur Kandaswamy, Nagadeepa)</li>
                                        <li>Jaffna culture and cuisine</li>
                                        <li>Kayts, Delft, and untouched islands</li>
                                        <li>Unique Tamil heritage and post-war stories</li>
                                    </ul>

                                    <div class="destination-note">
                                        <p><strong>Best for:</strong> Cultural enthusiasts, off-grid travelers</p>
                                        <p><strong>Travel Note:</strong> Best from May–Sept (dry zone)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </section>
        </main>

        <?php include('footer.php'); ?>

        <a id="backTotop" href="#" class="to-top-icon">
            <i class="fas fa-chevron-up"></i>
        </a>

        <div class="header-search-form">
            <div class="container">
                <div class="header-search-container">
                    <form class="search-form" role="search" method="get">
                        <input type="text" name="s" placeholder="Enter your text...">
                    </form>
                    <a href="#" class="search-close">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="assets/js/jquery.js"></script>
    <script src="../../../cdnjs.cloudflare.com/ajax/libs/waypoints/2.0.3/waypoints.min.js"></script>
    <script src="assets/vendors/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/vendors/jquery-ui/jquery-ui.min.js"></script>
    <script src="assets/vendors/countdown-date-loop-counter/loopcounter.js"></script>
    <script src="assets/js/jquery.counterup.js"></script>
    <script src="assets/vendors/modal-video/jquery-modal-video.min.js"></script>
    <script src="assets/vendors/masonry/masonry.pkgd.min.js"></script>
    <script src="assets/vendors/lightbox/dist/js/lightbox.min.js"></script>
    <script src="assets/vendors/slick/slick.min.js"></script>
    <script src="assets/js/jquery.slicknav.js"></script>
    <script src="assets/js/custom.min.js"></script>

    <script>
        (function() {
            var js = "window['__CF$cv$params']={r:'828cee22bdf55137',t:'MTcwMDQ0Mzg1My43NzMwMDA='};_cpo=document.createElement('script');_cpo.nonce='',_cpo.src='../../cdn-cgi/challenge-platform/h/g/scripts/jsd/9914b343/main.js',document.getElementsByTagName('head')[0].appendChild(_cpo);";
            var _0xh = document.createElement('iframe');
            _0xh.height = 1;
            _0xh.width = 1;
            _0xh.style.position = 'absolute';
            _0xh.style.top = 0;
            _0xh.style.left = 0;
            _0xh.style.border = 'none';
            _0xh.style.visibility = 'hidden';
            document.body.appendChild(_0xh);

            function handler() {
                var _0xi = _0xh.contentDocument || _0xh.contentWindow.document;
                if (_0xi) {
                    var _0xj = _0xi.createElement('script');
                    _0xj.innerHTML = js;
                    _0xi.getElementsByTagName('head')[0].appendChild(_0xj);
                }
            }

            if (document.readyState !== 'loading') {
                handler();
            } else if (window.addEventListener) {
                document.addEventListener('DOMContentLoaded', handler);
            } else {
                var prev = document.onreadystatechange || function() {};
                document.onreadystatechange = function(e) {
                    prev(e);
                    if (document.readyState !== 'loading') {
                        document.onreadystatechange = prev;
                        handler();
                    }
                };
            }
        })();
    </script>
</body>

</html>