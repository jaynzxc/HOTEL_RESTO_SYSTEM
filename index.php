<?php
require_once __DIR__ . '/controller/landing_get.php';
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo htmlspecialchars($hotelSettings['hotel_name'] ?? 'Hotel & Restaurant'); ?> - Luxury Stay &
            Dining</title>
        <!-- Tailwind CSS -->
        <script src="https://cdn.tailwindcss.com"></script>
        <!-- Font Awesome 6 -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <!-- AOS Animation Library -->
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .hero-pattern {
                background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.6) 100%);
            }

            .hover-scale {
                transition: transform 0.3s ease;
            }

            .hover-scale:hover {
                transform: translateY(-5px);
            }

            html {
                scroll-behavior: smooth;
            }

            ::-webkit-scrollbar {
                width: 8px;
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            ::-webkit-scrollbar-thumb {
                background: #d97706;
                border-radius: 4px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #b45309;
            }

            .loading {
                display: inline-block;
                width: 20px;
                height: 20px;
                border: 2px solid #f3f3f3;
                border-top: 2px solid #d97706;
                border-radius: 50%;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }
        </style>
    </head>

    <body class="font-sans antialiased bg-slate-50">

        <!-- ========== NAVIGATION ========== -->
        <nav
            class="bg-white/95 backdrop-blur-sm shadow-sm fixed w-full z-50 border-b border-amber-100 transition-all duration-300">
            <div class="container mx-auto px-6 py-4">
                <div class="flex items-center justify-between">
                    <!-- Logo -->
                    <a href="#home" class="flex items-center gap-2 text-amber-700 hover:opacity-80 transition">
                        <i class="fa-solid fa-utensils text-xl"></i>
                        <i class="fa-solid fa-bed text-xl"></i>
                        <span class="font-semibold text-xl tracking-tight text-slate-800 ml-1">Hotel and<span
                                class="text-amber-600"> Restaurant</span></span>
                    </a>

                    <!-- Desktop Menu -->
                    <div class="hidden md:flex items-center space-x-8">
                        <a href="#home" class="text-slate-600 hover:text-amber-700 transition font-medium">Home</a>
                        <a href="#rooms" class="text-slate-600 hover:text-amber-700 transition font-medium">Rooms</a>
                        <a href="#dining" class="text-slate-600 hover:text-amber-700 transition font-medium">Dining</a>
                        <a href="#offers" class="text-slate-600 hover:text-amber-700 transition font-medium">Offers</a>
                        <a href="#about" class="text-slate-600 hover:text-amber-700 transition font-medium">About</a>
                        <a href="#contact"
                            class="text-slate-600 hover:text-amber-700 transition font-medium">Contact</a>
                    </div>

                    <!-- Auth Buttons -->
                    <div class="flex items-center space-x-4">
                        <?php if ($isLoggedIn): ?>
                            <div class="relative group">
                                <button
                                    class="hidden md:flex items-center gap-2 text-slate-600 hover:text-amber-700 transition">
                                    <i class="fas fa-circle-user text-xl"></i>
                                    <span class="font-medium"><?php echo htmlspecialchars($userName); ?></span>
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </button>
                                <div
                                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-slate-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                    <div class="p-2">
                                        <div class="px-4 py-2 text-sm text-slate-500 border-b border-slate-100">
                                            <i class="fas fa-user mr-2"></i> <?php echo htmlspecialchars($userName); ?>
                                        </div>
                                        <a href="<?php echo $userRole === 'admin' || $userRole === 'staff' ? './src/admin_portal/dashboard.php' : './src/customer_portal/dashboard.php'; ?>"
                                            class="block px-4 py-2 text-sm text-slate-700 hover:bg-amber-50 hover:text-amber-700 rounded-lg transition">
                                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                                        </a>
                                        <a href="../../controller/auth/logout.php"
                                            class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <a href="../../controller/auth/logout.php"
                                class="md:hidden bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 transition text-sm">
                                <i class="fa-solid fa-sign-out-alt mr-1"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="./src/login-register/login_form.php"
                                class="hidden md:block text-slate-600 hover:text-amber-700 transition font-medium">
                                <i class="fas fa-circle-user mr-1"></i> Login
                            </a>
                            <a href="./src/login-register/register_form.php"
                                class="bg-amber-600 text-white px-6 py-2 rounded-xl hover:bg-amber-700 transition shadow-sm hover:shadow font-medium">
                                Sign Up
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Mobile Menu Button -->
                    <button id="mobileMenuBtn" class="md:hidden text-slate-600 hover:text-amber-700 transition">
                        <i class="fa-solid fa-bars text-2xl"></i>
                    </button>
                </div>

                <!-- Mobile Menu -->
                <div id="mobileMenu" class="hidden md:hidden mt-4 pb-4 border-t border-slate-100">
                    <div class="flex flex-col space-y-3 pt-4">
                        <a href="#home" class="text-slate-600 hover:text-amber-700 transition py-2">Home</a>
                        <a href="#rooms" class="text-slate-600 hover:text-amber-700 transition py-2">Rooms</a>
                        <a href="#dining" class="text-slate-600 hover:text-amber-700 transition py-2">Dining</a>
                        <a href="#offers" class="text-slate-600 hover:text-amber-700 transition py-2">Offers</a>
                        <a href="#about" class="text-slate-600 hover:text-amber-700 transition py-2">About</a>
                        <a href="#contact" class="text-slate-600 hover:text-amber-700 transition py-2">Contact</a>
                        <?php if (!$isLoggedIn): ?>
                            <a href="./src/login-register/login_form.php" class="text-amber-600 font-medium py-2">Login</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- ========== HERO SECTION ========== -->
        <section id="home" class="relative h-screen bg-cover bg-center"
            style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');">
            <div class="hero-pattern absolute inset-0"></div>

            <div class="container mx-auto px-6 h-full flex items-center relative z-10">
                <div class="text-white max-w-3xl" data-aos="fade-up" data-aos-duration="1000">
                    <span
                        class="bg-amber-500/20 backdrop-blur-sm text-amber-200 px-4 py-2 rounded-full text-sm font-medium inline-block mb-6 border border-amber-400/30">
                        <i class="fas fa-star mr-2"></i>Welcome to
                        <?php echo htmlspecialchars($hotelSettings['hotel_name'] ?? 'Hotel and Restaurant'); ?>
                    </span>
                    <h1 class="text-5xl md:text-7xl font-light mb-4 tracking-tight">
                        stay.<span class="font-semibold text-amber-400">dine</span>.relax.
                    </h1>
                    <p class="text-xl mb-10 text-gray-200 max-w-2xl leading-relaxed">
                        Book your stay, reserve a table, or order online. Your comfort is our priority.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <a href="./src/customer_portal/hotel_booking.php"
                            class="bg-amber-600 text-white px-8 py-4 rounded-xl text-lg font-medium hover:bg-amber-700 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                            <i class="fas fa-calendar-check"></i> Book a Room
                        </a>
                        <a href="#dining"
                            class="bg-white/10 backdrop-blur-sm text-white px-8 py-4 rounded-xl text-lg font-medium hover:bg-white/20 transition border border-white/30 flex items-center gap-2">
                            <i class="fas fa-clock"></i> Reserve a Table
                        </a>
                    </div>

                    <!-- Quick Stats from Database -->
                    <div class="flex mt-16 space-x-8" data-aos="fade-up" data-aos-delay="200">
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                            <p class="text-3xl font-bold text-amber-400 counter"
                                data-target="<?php echo $stats['total_guests']; ?>">0</p>
                            <p class="text-sm text-gray-300">Happy Guests</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                            <p class="text-3xl font-bold text-amber-400 counter"
                                data-target="<?php echo $stats['total_rooms']; ?>">0</p>
                            <p class="text-sm text-gray-300">Luxury Rooms</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm rounded-2xl px-6 py-4 border border-white/20">
                            <p class="text-3xl font-bold text-amber-400 counter"
                                data-target="<?php echo $stats['total_menu_items']; ?>">0</p>
                            <p class="text-sm text-gray-300">Menu Items</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
                <a href="#rooms" class="text-white hover:text-amber-400 transition">
                    <i class="fas fa-chevron-down text-2xl"></i>
                </a>
            </div>
        </section>

        <!-- ========== FEATURED ROOMS SECTION ========== -->
        <section id="rooms" class="py-32 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12" data-aos="fade-up">
                    <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">Luxury stays</span>
                    <h2 class="text-4xl font-light text-slate-800 mt-2">featured <span
                            class="font-semibold text-amber-700">rooms</span></h2>
                    <p class="text-slate-500 max-w-2xl mx-auto mt-4">Choose from our selection of premium rooms designed
                        for your comfort.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <?php foreach ($featuredRooms as $index => $room): ?>
                        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover-scale shadow-sm hover:shadow-xl transition"
                            data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <img src="<?php echo htmlspecialchars($room['image_url'] ?? 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80'); ?>"
                                alt="<?php echo htmlspecialchars($room['name']); ?>" class="w-full h-64 object-cover">
                            <div class="p-6">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <h3 class="text-xl font-semibold text-slate-800">
                                            <?php echo htmlspecialchars($room['name']); ?></h3>
                                        <p class="text-sm text-slate-500">
                                            <?php echo htmlspecialchars($room['beds'] ?? $room['description']); ?></p>
                                    </div>
                                    <span
                                        class="bg-amber-50 text-amber-700 font-bold px-3 py-1 rounded-full text-sm">₱<?php echo number_format($room['price']); ?></span>
                                </div>
                                <div class="flex items-center space-x-3 mb-4 text-sm text-slate-500">
                                    <?php
                                    $amenities = explode(',', $room['amenities'] ?? '');
                                    $displayAmenities = array_slice($amenities, 0, 3);
                                    foreach ($displayAmenities as $amenity):
                                        ?>
                                        <span><i
                                                class="fa-solid <?php echo strpos($amenity, 'WiFi') !== false ? 'fa-wifi' : (strpos($amenity, 'Breakfast') !== false ? 'fa-mug-saucer' : 'fa-tv'); ?> text-amber-600 mr-1"></i>
                                            <?php echo trim($amenity); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <a href="./src/customer_portal/hotel_booking.php?room=<?php echo $room['id']; ?>"
                                    class="w-full block text-center border border-amber-600 text-amber-700 py-2 rounded-xl hover:bg-amber-50 transition font-medium">
                                    Book Now
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="text-center mt-8" data-aos="fade-up">
                    <a href="./src/customer_portal/hotel_booking.php"
                        class="text-amber-700 hover:text-amber-800 font-medium inline-flex items-center gap-2">
                        View All Rooms <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </section>

        <!-- ========== DINING SECTION ========== -->
        <section id="dining" class="py-20 bg-white border-y border-amber-100">
            <div class="container mx-auto px-6">
                <div class="flex flex-col lg:flex-row items-center gap-12">
                    <div class="lg:w-1/2" data-aos="fade-right">
                        <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">Culinary
                            experience</span>
                        <h2 class="text-4xl font-light text-slate-800 mt-2 mb-4">dine with <span
                                class="font-semibold text-amber-700">us</span></h2>
                        <p class="text-slate-600 mb-6 leading-relaxed">Experience exceptional dining with our chef's
                            special creations. From local favorites to international cuisine, we bring you the finest
                            ingredients and flavors.</p>

                        <div class="grid grid-cols-2 gap-4 mb-8">
                            <?php foreach ($menuItems as $item): ?>
                                <div class="flex items-center gap-2 bg-slate-50 p-3 rounded-xl">
                                    <i class="fas fa-circle-check text-amber-600"></i>
                                    <span class="text-sm"><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="flex flex-wrap gap-4">
                            <a href="./src/customer_portal/order_food.php"
                                class="bg-amber-600 text-white px-6 py-3 rounded-xl hover:bg-amber-700 transition font-medium shadow-md flex items-center gap-2">
                                <i class="fas fa-calendar-check"></i> Order Now
                            </a>
                            <a href="./src/customer_portal/order_food.php"
                                class="border-2 border-amber-600 text-amber-700 px-6 py-3 rounded-xl hover:bg-amber-50 transition font-medium flex items-center gap-2">
                                <i class="fas fa-rectangle-list"></i> View Menu
                            </a>
                        </div>
                    </div>
                    <div class="lg:w-1/2" data-aos="fade-left">
                        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="Restaurant" class="rounded-2xl shadow-2xl border-4 border-white">
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== AMENITIES GRID ========== -->
        <section id="amenities" class="py-20 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12" data-aos="fade-up">
                    <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">amenities</span>
                    <h2 class="text-4xl font-light text-slate-800 mt-2">everything you <span
                            class="font-semibold text-amber-700">need</span></h2>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <?php
                    $amenities = [
                        ['icon' => 'fa-water-ladder', 'name' => 'Pool'],
                        ['icon' => 'fa-dumbbell', 'name' => 'Gym'],
                        ['icon' => 'fa-spa', 'name' => 'Spa'],
                        ['icon' => 'fa-wifi', 'name' => 'Free WiFi'],
                        ['icon' => 'fa-car', 'name' => 'Parking'],
                        ['icon' => 'fa-bell-concierge', 'name' => '24/7 Service'],
                        ['icon' => 'fa-van-shuttle', 'name' => 'Shuttle'],
                        ['icon' => 'fa-baby', 'name' => 'Baby Sitting']
                    ];
                    foreach ($amenities as $index => $amenity):
                        ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 text-center hover-scale shadow-sm hover:shadow-md transition"
                            data-aos="fade-up" data-aos-delay="<?php echo $index * 50; ?>">
                            <div class="h-14 w-14 bg-amber-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid <?php echo $amenity['icon']; ?> text-2xl text-amber-700"></i>
                            </div>
                            <h3 class="font-medium"><?php echo $amenity['name']; ?></h3>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========== SPECIAL OFFERS ========== -->
        <section id="offers" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12" data-aos="fade-up">
                    <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">promos</span>
                    <h2 class="text-4xl font-light text-slate-800 mt-2">special <span
                            class="font-semibold text-amber-700">offers</span></h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($activePromos as $index => $promo): ?>
                        <div class="bg-gradient-to-r from-amber-50 to-amber-100/50 p-6 rounded-2xl border border-amber-200 flex items-center gap-4 hover-scale transition"
                            data-aos="fade-<?php echo $index % 2 == 0 ? 'right' : 'left'; ?>">
                            <div class="h-16 w-16 bg-amber-600/10 rounded-xl flex items-center justify-center">
                                <i class="fa-solid fa-tag text-3xl text-amber-700"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($promo['campaign_name']); ?>
                                </h3>
                                <p class="text-slate-600"><?php echo htmlspecialchars($promo['description']); ?></p>
                                <?php if ($promo['discount_percent']): ?>
                                    <span class="text-sm text-amber-700 font-medium"><?php echo $promo['discount_percent']; ?>%
                                        off</span>
                                <?php elseif ($promo['discount_amount']): ?>
                                    <span
                                        class="text-sm text-amber-700 font-medium">₱<?php echo number_format($promo['discount_amount']); ?>
                                        off</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========== TESTIMONIALS ========== -->
        <section class="py-20 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12" data-aos="fade-up">
                    <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">testimonials</span>
                    <h2 class="text-4xl font-light text-slate-800 mt-2">what our <span
                            class="font-semibold text-amber-700">guests say</span></h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <?php foreach ($testimonials as $index => $testimonial): ?>
                        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm hover-scale transition"
                            data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                            <div class="flex items-center gap-3 mb-4">
                                <div
                                    class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-xl">
                                    <?php
                                    $nameParts = explode(' ', $testimonial['full_name']);
                                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                                    if (isset($nameParts[1]))
                                        $initials .= strtoupper(substr($nameParts[1], 0, 1));
                                    echo $initials;
                                    ?>
                                </div>
                                <div>
                                    <h4 class="font-semibold"><?php echo htmlspecialchars($testimonial['full_name']); ?>
                                    </h4>
                                    <div class="flex text-amber-400">
                                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                            <i class="fa-solid fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <p class="text-slate-600">"<?php echo htmlspecialchars($testimonial['review_text']); ?>"</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- ========== CALL TO ACTION ========== -->
        <section class="bg-amber-600 py-20">
            <div class="container mx-auto px-6 text-center" data-aos="zoom-in">
                <h2 class="text-4xl font-light text-white mb-4">ready to <span class="font-semibold">experience</span>
                    luxury?</h2>
                <p class="text-amber-100 mb-8 max-w-2xl mx-auto">Join our community and get exclusive offers, early
                    access to promotions, and earn loyalty points.</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <?php if (!$isLoggedIn): ?>
                        <a href="./src/login-register/register_form.php"
                            class="bg-white text-amber-700 px-8 py-4 rounded-xl text-lg font-medium hover:bg-slate-100 transition shadow-lg hover:shadow-xl flex items-center gap-2">
                            <i class="fas fa-user"></i> Create Account
                        </a>
                    <?php endif; ?>
                    <a href="#contact"
                        class="border-2 border-white text-white px-8 py-4 rounded-xl text-lg font-medium hover:bg-white/10 transition flex items-center gap-2">
                        <i class="fas fa-circle-question"></i> Contact Us
                    </a>
                </div>
            </div>
        </section>

        <!-- ========== ABOUT & CONTACT SECTION ========== -->
        <section id="about" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    <div data-aos="fade-right">
                        <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">About Us</span>
                        <h2 class="text-3xl font-light text-slate-800 mt-2 mb-4">your home away from <span
                                class="font-semibold text-amber-700">home</span></h2>
                        <p class="text-slate-600 leading-relaxed mb-4">Founded with a passion for hospitality,
                            <?php echo htmlspecialchars($hotelSettings['hotel_name'] ?? 'Hotel and Restaurant'); ?> has
                            been providing exceptional service and comfort since 2024. We believe in creating memorable
                            experiences for every guest.</p>
                        <p class="text-slate-600 leading-relaxed">Our dedicated team is committed to ensuring your stay
                            is nothing short of perfect. From our luxurious rooms to our award-winning restaurant, we
                            strive for excellence in everything we do.</p>
                    </div>
                    <div data-aos="fade-left">
                        <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                            alt="Hotel Lobby" class="rounded-2xl shadow-xl">
                    </div>
                </div>
            </div>
        </section>

        <section id="contact" class="py-20 bg-slate-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12" data-aos="fade-up">
                    <span class="text-amber-600 font-medium text-sm tracking-wider uppercase">Get in touch</span>
                    <h2 class="text-4xl font-light text-slate-800 mt-2">contact <span
                            class="font-semibold text-amber-700">us</span></h2>
                    <p class="text-slate-500 max-w-2xl mx-auto mt-4">We'd love to hear from you. Reach out for
                        inquiries, reservations, or feedback.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
                    <div class="text-center p-6 bg-white rounded-2xl border border-slate-200" data-aos="fade-up"
                        data-aos-delay="0">
                        <div class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-map-pin text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold mb-1">Address</h3>
                        <p class="text-slate-500 text-sm">
                            <?php echo htmlspecialchars($hotelSettings['hotel_address'] ?? '123 Main St, Manila, Philippines'); ?>
                        </p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-2xl border border-slate-200" data-aos="fade-up"
                        data-aos-delay="100">
                        <div class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-phone text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold mb-1">Phone</h3>
                        <p class="text-slate-500 text-sm">
                            <?php echo htmlspecialchars($hotelSettings['hotel_contact'] ?? '+63 (2) 1234 5678'); ?></p>
                    </div>
                    <div class="text-center p-6 bg-white rounded-2xl border border-slate-200" data-aos="fade-up"
                        data-aos-delay="200">
                        <div class="h-12 w-12 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-envelope text-amber-600"></i>
                        </div>
                        <h3 class="font-semibold mb-1">Email</h3>
                        <p class="text-slate-500 text-sm">
                            <?php echo htmlspecialchars($hotelSettings['hotel_email'] ?? 'info@hotelandrestaurant.com'); ?>
                        </p>
                    </div>
                </div>

                <form id="contactForm"
                    class="max-w-2xl mx-auto bg-white rounded-2xl p-8 shadow-lg border border-slate-200"
                    data-aos="fade-up">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Name *</label>
                            <input type="text" id="contactName" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Email *</label>
                            <input type="email" id="contactEmail" required
                                class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Subject</label>
                        <input type="text" id="contactSubject"
                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Message *</label>
                        <textarea id="contactMessage" rows="4" required
                            class="w-full border border-slate-200 rounded-xl px-4 py-2 focus:ring-1 focus:ring-amber-500 outline-none"></textarea>
                    </div>
                    <button type="submit" id="sendMessageBtn"
                        class="w-full bg-amber-600 text-white py-3 rounded-xl hover:bg-amber-700 transition font-medium">
                        Send Message
                    </button>
                </form>
            </div>
        </section>

        <!-- ========== FOOTER ========== -->
        <footer class="bg-white border-t border-amber-100 py-12">
            <div class="container mx-auto px-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center gap-2 text-amber-700 mb-4">
                            <i class="fa-solid fa-utensils"></i>
                            <i class="fa-solid fa-bed"></i>
                            <span class="font-semibold text-xl text-slate-800">Hotel and<span class="text-amber-600">
                                    Restaurant</span></span>
                        </div>
                        <p class="text-slate-500 text-sm">Experience luxury and comfort in the heart of the city.</p>
                        <div class="flex space-x-3 mt-4">
                            <a href="#" class="text-slate-400 hover:text-amber-600 transition"><i
                                    class="fa-brands fa-facebook text-xl"></i></a>
                            <a href="#" class="text-slate-400 hover:text-amber-600 transition"><i
                                    class="fa-brands fa-instagram text-xl"></i></a>
                            <a href="#" class="text-slate-400 hover:text-amber-600 transition"><i
                                    class="fa-brands fa-x-twitter text-xl"></i></a>
                        </div>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-4">Quick Links</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#home" class="text-slate-500 hover:text-amber-600 transition">Home</a></li>
                            <li><a href="#rooms" class="text-slate-500 hover:text-amber-600 transition">Rooms</a></li>
                            <li><a href="#dining" class="text-slate-500 hover:text-amber-600 transition">Dining</a></li>
                            <li><a href="#about" class="text-slate-500 hover:text-amber-600 transition">About</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-4">Support</h4>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-slate-500 hover:text-amber-600 transition">FAQ</a></li>
                            <li><a href="#" class="text-slate-500 hover:text-amber-600 transition">Privacy Policy</a>
                            </li>
                            <li><a href="#" class="text-slate-500 hover:text-amber-600 transition">Terms of Service</a>
                            </li>
                            <li><a href="#" class="text-slate-500 hover:text-amber-600 transition">Help Center</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="font-semibold text-slate-700 mb-4">Contact Info</h4>
                        <ul class="space-y-2 text-sm text-slate-500">
                            <li><i class="fas fa-map-pin mr-2 text-amber-600"></i>
                                <?php echo htmlspecialchars($hotelSettings['hotel_address'] ?? '123 Main St, Manila'); ?>
                            </li>
                            <li><i class="fas fa-phone mr-2 text-amber-600"></i>
                                <?php echo htmlspecialchars($hotelSettings['hotel_contact'] ?? '+63 (2) 1234 5678'); ?>
                            </li>
                            <li><i class="fas fa-envelope mr-2 text-amber-600"></i>
                                <?php echo htmlspecialchars($hotelSettings['hotel_email'] ?? 'info@hotelandrestaurant.com'); ?>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-amber-100 mt-8 pt-8 text-center text-slate-400 text-sm">
                    <p>&copy; <span id="currentYear"></span> HNR · Hotel & Restaurant. All rights reserved.</p>
                </div>
            </div>
        </footer>

        <script>
            // Initialize AOS
            AOS.init({
                duration: 800,
                once: true,
                offset: 100
            });

            // Counter animation
            function animateCounters() {
                const counters = document.querySelectorAll('.counter');
                counters.forEach(counter => {
                    const target = parseInt(counter.getAttribute('data-target'));
                    let current = 0;
                    const increment = target / 50;
                    const updateCounter = () => {
                        if (current < target) {
                            current += increment;
                            counter.textContent = Math.ceil(current);
                            setTimeout(updateCounter, 20);
                        } else {
                            counter.textContent = target;
                        }
                    };
                    updateCounter();
                });
            }

            // Mobile menu toggle
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                        }
                    }
                });
            });

            // Navbar background change on scroll
            const nav = document.querySelector('nav');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 100) {
                    nav.classList.add('shadow-md', 'bg-white');
                    nav.classList.remove('bg-white/95');
                } else {
                    nav.classList.remove('shadow-md');
                    nav.classList.add('bg-white/95');
                }
            });

            // Contact form submission
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const name = document.getElementById('contactName').value.trim();
                    const email = document.getElementById('contactEmail').value.trim();
                    const subject = document.getElementById('contactSubject').value.trim() || 'Inquiry';
                    const message = document.getElementById('contactMessage').value.trim();

                    if (!name || !email || !message) {
                        Swal.fire({
                            title: 'Missing Fields',
                            text: 'Please fill in all required fields.',
                            icon: 'error',
                            confirmButtonColor: '#d97706'
                        });
                        return;
                    }

                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        Swal.fire({
                            title: 'Invalid Email',
                            text: 'Please enter a valid email address.',
                            icon: 'error',
                            confirmButtonColor: '#d97706'
                        });
                        return;
                    }

                    const submitBtn = document.getElementById('sendMessageBtn');
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<div class="loading mx-auto"></div> Sending...';

                    // Simulate form submission (replace with actual API call)
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Message Sent!',
                            text: 'Thank you for reaching out. We\'ll get back to you soon.',
                            icon: 'success',
                            confirmButtonColor: '#d97706'
                        });
                        contactForm.reset();
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Send Message';
                    }, 1500);
                });
            }

            // Set current year in footer
            document.getElementById('currentYear').textContent = new Date().getFullYear();

            // Trigger counter animation when visible
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        animateCounters();
                        observer.unobserve(entry.target);
                    }
                });
            });

            const statsContainer = document.querySelector('.flex.mt-16');
            if (statsContainer) {
                observer.observe(statsContainer);
            }
        </script>
    </body>

</html>