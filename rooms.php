<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Rooms - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <img src="assets/images/logo.png" alt="Villa Adrian Logo" class="logo">
                <span><?php echo SITE_NAME; ?></span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="rooms.php" class="nav-link active">Rooms</a></li>
                <li><a href="about.php" class="nav-link">About Ksamil</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
                <?php if (isLoggedIn()): ?>
                    <li><a href="user/index.php" class="nav-link">Dashboard</a></li>
                    <li><a href="user/logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                    <li><a href="register.php" class="nav-link">Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- Rooms Header -->
    <section class="page-header">
        <div class="container">
            <h1>Our Rooms & Suites</h1>
            <p>Experience luxury and comfort in the heart of Ksamil</p>
        </div>
    </section>

    <!-- Rooms Filter -->
    <section class="rooms-filter">
        <div class="container">
            <form class="filter-form" id="roomsFilter">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Room Type</label>
                        <select id="roomTypeFilter">
                            <option value="">All Room Types</option>
                            <option value="one_bedroom_apartment">One-Bedroom Apartment</option>
                            <option value="deluxe_double">Deluxe Double Room</option>
                            <option value="deluxe_triple">Deluxe Triple Room</option>
                            <option value="deluxe_quadruple">Deluxe Quadruple Room</option>
                            <option value="suite">Suite</option>
                            <option value="deluxe_studio">Deluxe Studio</option>
                            <option value="family_studio">Family Studio</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Max Guests</label>
                        <select id="guestsFilter">
                            <option value="">Any Number</option>
                            <option value="1">1 Guest</option>
                            <option value="2">2 Guests</option>
                            <option value="3">3 Guests</option>
                            <option value="4">4 Guests</option>
                            <option value="5">5+ Guests</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Price Range</label>
                        <select id="priceFilter">
                            <option value="">Any Price</option>
                            <option value="0-100">Under €100</option>
                            <option value="100-150">€100 - €150</option>
                            <option value="150-200">€150 - €200</option>
                            <option value="200-250">€200 - €250</option>
                            <option value="250-999">Over €250</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Rooms Grid -->
    <section class="rooms-grid-section">
        <div class="container">
            <div id="roomsContainer" class="rooms-grid">
                <?php
                $query = "SELECT * FROM rooms WHERE is_available = 1 ORDER BY price_per_night";
                $stmt = $db->prepare($query);
                $stmt->execute();
                
                while ($room = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="room-card" data-type="<?php echo $room['room_type']; ?>" 
                     data-guests="<?php echo $room['max_guests']; ?>" 
                     data-price="<?php echo $room['price_per_night']; ?>">
                    <div class="room-image">
                        <img src="assets/images/rooms/<?php echo $room['id']; ?>/1.jpg" 
                             alt="<?php echo getRoomTypeName($room['room_type']); ?>"
                             onerror="this.src='assets/images/rooms/default.jpg'">
                        <div class="room-overlay">
                            <span class="room-price">€<?php echo $room['price_per_night']; ?>/night</span>
                        </div>
                    </div>
                    
                    <div class="room-info">
                        <h3><?php echo getRoomTypeName($room['room_type']); ?></h3>
                        <p class="room-number">Room <?php echo $room['room_number']; ?></p>
                        
                        <div class="room-features">
                            <span class="feature">👥 <?php echo $room['max_guests']; ?> Guests</span>
                            <span class="feature">🛏️ 
                                <?php
                                switch($room['room_type']) {
                                    case 'one_bedroom_apartment':
                                    case 'deluxe_quadruple':
                                    case 'family_studio':
                                        echo '2 Twin + 1 Full';
                                        break;
                                    case 'deluxe_double':
                                        echo '1 Queen';
                                        break;
                                    case 'deluxe_triple':
                                    case 'deluxe_studio':
                                        echo '1 Twin + 1 Queen';
                                        break;
                                    case 'suite':
                                        echo '2 Twin + 1 Queen + 1 Sofa';
                                        break;
                                    default:
                                        echo 'Various';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <p class="room-description"><?php echo $room['description']; ?></p>
                        
                        <div class="room-amenities">
                            <?php
                            $amenities = json_decode($room['amenities'] ?? '[]', true);
                            if ($amenities) {
                                foreach (array_slice($amenities, 0, 4) as $amenity) {
                                    echo '<span class="amenity-tag">' . $amenity . '</span>';
                                }
                                if (count($amenities) > 4) {
                                    echo '<span class="amenity-tag">+' . (count($amenities) - 4) . ' more</span>';
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="room-actions">
                            <a href="user/book.php?room_id=<?php echo $room['id']; ?>" class="btn btn-primary">
                                Book Now
                            </a>
                            <button class="btn btn-secondary" onclick="viewRoomDetails(<?php echo $room['id']; ?>)">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            
            <?php if ($stmt->rowCount() === 0): ?>
            <div class="no-rooms">
                <h3>No Rooms Available</h3>
                <p>Please check back later for available rooms.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Room Details Modal -->
    <div id="roomModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeRoomModal()">&times;</span>
            <div id="roomModalContent"></div>
        </div>
    </div>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Book Your Stay?</h2>
                <p>Experience the best of Ksamil with our luxury accommodations</p>
                <?php if (isLoggedIn()): ?>
                    <a href="user/book.php" class="btn btn-primary btn-large">Book Your Room</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-primary btn-large">Create Account to Book</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
    // Room filtering
    document.addEventListener('DOMContentLoaded', function() {
        const filters = ['roomTypeFilter', 'guestsFilter', 'priceFilter'];
        
        filters.forEach(filterId => {
            const filter = document.getElementById(filterId);
            if (filter) {
                filter.addEventListener('change', filterRooms);
            }
        });
    });

    function filterRooms() {
        const roomType = document.getElementById('roomTypeFilter').value;
        const guests = document.getElementById('guestsFilter').value;
        const priceRange = document.getElementById('priceFilter').value;
        
        const rooms = document.querySelectorAll('.room-card');
        let visibleCount = 0;
        
        rooms.forEach(room => {
            const roomTypeMatch = !roomType || room.dataset.type === roomType;
            const guestsMatch = !guests || (
                guests === '5' ? parseInt(room.dataset.guests) >= 5 : parseInt(room.dataset.guests) == guests
            );
            
            let priceMatch = true;
            if (priceRange) {
                const [min, max] = priceRange.split('-').map(Number);
                const roomPrice = parseFloat(room.dataset.price);
                priceMatch = roomPrice >= min && roomPrice <= max;
            }
            
            if (roomTypeMatch && guestsMatch && priceMatch) {
                room.style.display = 'block';
                visibleCount++;
            } else {
                room.style.display = 'none';
            }
        });
        
        // Show/hide no results message
        const noRooms = document.querySelector('.no-rooms');
        if (noRooms) {
            noRooms.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    }

    function viewRoomDetails(roomId) {
        fetch(`api/get_room_details.php?id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const room = data.room;
                    const modal = document.getElementById('roomModal');
                    const content = document.getElementById('roomModalContent');
                    
                    content.innerHTML = `
                        <div class="room-details">
                            <h2>${room.room_type_name}</h2>
                            <div class="room-gallery">
                                <img src="assets/images/rooms/${room.id}/1.jpg" alt="${room.room_type_name}">
                                <!-- More images would go here -->
                            </div>
                            
                            <div class="details-grid">
                                <div class="detail-section">
                                    <h3>Room Information</h3>
                                    <p><strong>Room Number:</strong> ${room.room_number}</p>
                                    <p><strong>Max Guests:</strong> ${room.max_guests}</p>
                                    <p><strong>Price per Night:</strong> €${room.price_per_night}</p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3>Description</h3>
                                    <p>${room.description}</p>
                                </div>
                                
                                <div class="detail-section">
                                    <h3>Amenities</h3>
                                    <div class="amenities-list">
                                        ${room.amenities ? room.amenities.map(amenity => 
                                            `<span class="amenity-tag">${amenity}</span>`
                                        ).join('') : 'No amenities listed'}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="room-actions">
                                <a href="user/book.php?room_id=${room.id}" class="btn btn-primary">Book This Room</a>
                                <button class="btn btn-secondary" onclick="closeRoomModal()">Close</button>
                            </div>
                        </div>
                    `;
                    
                    modal.style.display = 'block';
                } else {
                    alert('Error loading room details: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while loading room details.');
            });
    }

    function closeRoomModal() {
        document.getElementById('roomModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('roomModal');
        if (event.target === modal) {
            closeRoomModal();
        }
    }
    </script>

    <style>
    .page-header {
        background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('assets/images/hotel/rooms-header.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 4rem 0;
        text-align: center;
        margin-top: 60px;
    }

    .page-header h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .rooms-filter {
        background: #f8f9fa;
        padding: 2rem 0;
    }

    .filter-row {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .filter-group label {
        font-weight: 600;
        color: #2c3e50;
    }

    .filter-group select {
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 5px;
        min-width: 150px;
    }

    .rooms-grid-section {
        padding: 4rem 0;
    }

    .room-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s;
    }

    .room-card:hover {
        transform: translateY(-5px);
    }

    .room-image {
        position: relative;
        height: 250px;
        overflow: hidden;
    }

    .room-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s;
    }

    .room-card:hover .room-image img {
        transform: scale(1.05);
    }

    .room-overlay {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(52, 152, 219, 0.9);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
    }

    .room-info {
        padding: 1.5rem;
    }

    .room-info h3 {
        color: #2c3e50;
        margin-bottom: 0.5rem;
    }

    .room-number {
        color: #7f8c8d;
        font-size: 0.9rem;
        margin-bottom: 1rem;
    }

    .room-features {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .feature {
        font-size: 0.9rem;
        color: #555;
    }

    .room-description {
        color: #666;
        margin-bottom: 1rem;
        line-height: 1.5;
    }

    .room-amenities {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .amenity-tag {
        background: #ecf0f1;
        padding: 0.25rem 0.75rem;
        border-radius: 15px;
        font-size: 0.8rem;
        color: #2c3e50;
    }

    .room-actions {
        display: flex;
        gap: 0.5rem;
    }

    .cta-section {
        background: linear-gradient(135deg, #3498db, #2c3e50);
        color: white;
        padding: 4rem 0;
        text-align: center;
    }

    .cta-content h2 {
        margin-bottom: 1rem;
    }

    .cta-content p {
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    .btn-large {
        padding: 1rem 2rem;
        font-size: 1.1rem;
    }

    .modal-content {
        background-color: white;
        margin: 5% auto;
        padding: 2rem;
        border-radius: 10px;
        width: 90%;
        max-width: 800px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .room-details h2 {
        color: #2c3e50;
        margin-bottom: 1.5rem;
    }

    .room-gallery {
        margin-bottom: 2rem;
    }

    .room-gallery img {
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 8px;
    }

    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .detail-section h3 {
        color: #3498db;
        margin-bottom: 1rem;
    }

    .amenities-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    @media (max-width: 768px) {
        .filter-row {
            flex-direction: column;
            align-items: center;
        }
        
        .details-grid {
            grid-template-columns: 1fr;
        }
        
        .room-actions {
            flex-direction: column;
        }
    }
    </style>
</body>
</html>