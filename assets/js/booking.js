// Booking functionality
class BookingManager {
    constructor() {
        this.initEventListeners();
    }

    initEventListeners() {
        // Availability form
        const availabilityForm = document.getElementById('availabilityForm');
        if (availabilityForm) {
            availabilityForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.checkAvailability();
            });
        }

        // Booking form
        const bookingForm = document.getElementById('bookingForm');
        if (bookingForm) {
            bookingForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.processBooking();
            });
        }

        // Modal close
        const closeBtn = document.querySelector('.close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.closeBookingModal();
            });
        }

        // Date validation
        const checkInInput = document.getElementById('check_in');
        const checkOutInput = document.getElementById('check_out');
        
        if (checkInInput && checkOutInput) {
            checkInInput.addEventListener('change', () => {
                const checkInDate = new Date(checkInInput.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                
                checkOutInput.min = nextDay.toISOString().split('T')[0];
                
                if (new Date(checkOutInput.value) <= checkInDate) {
                    checkOutInput.value = nextDay.toISOString().split('T')[0];
                }
            });
        }
    }

    checkAvailability() {
        const formData = new FormData(document.getElementById('availabilityForm'));
        const data = {
            check_in: formData.get('check_in'),
            check_out: formData.get('check_out'),
            guests: formData.get('guests'),
            room_type: formData.get('room_type')
        };

        // Show loading state
        const submitBtn = document.querySelector('#availabilityForm button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duke kontrolluar...';
        submitBtn.disabled = true;

        fetch('../api/check_availability.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.displayAvailableRooms(result.available_rooms, result);
            } else {
                alert('Gabim: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ndodhi një gabim gjatë kontrollit të disponueshmërisë');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }

    displayAvailableRooms(rooms, searchData) {
        const resultsSection = document.getElementById('resultsSection');
        const availableRooms = document.getElementById('availableRooms');

        if (rooms.length === 0) {
            availableRooms.innerHTML = `
                <div class="no-rooms">
                    <i class="fas fa-bed"></i>
                    <h3>Nuk ka dhoma të disponueshme</h3>
                    <p>Ju lutemi provoni data të tjera ose kritere të ndryshme kërkimi.</p>
                </div>
            `;
        } else {
            let roomsHTML = '';
            
            rooms.forEach(room => {
                // Get room photos
                this.getRoomPhotos(room.id).then(photos => {
                    let sliderHTML = '';
                    
                    if (photos && photos.length > 0) {
                        // Build slider with actual photos
                        sliderHTML = photos.map((photo, index) => `
                            <div class="slide ${index === 0 ? 'active' : ''}">
                                <img src="../assets/images/rooms/uploads/${photo.photo_filename}" 
                                     alt="${room.room_type_name}"
                                     onerror="this.src='../assets/images/hotel/rooms-header.jpg'">
                            </div>
                        `).join('');
                        
                        // Add navigation if multiple photos
                        if (photos.length > 1) {
                            sliderHTML += `
                                <button class="slider-btn prev" onclick="moveSlide(this, -1)">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="slider-btn next" onclick="moveSlide(this, 1)">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                                <div class="slider-dots">
                                    ${photos.map((photo, index) => `
                                        <span class="dot ${index === 0 ? 'active' : ''}" 
                                              onclick="goToSlide(this, ${index})"></span>
                                    `).join('')}
                                </div>
                            `;
                        }
                    } else {
                        // Fallback image
                        sliderHTML = `
                            <div class="slide active">
                                <img src="../assets/images/hotel/rooms-header.jpg" 
                                     alt="${room.room_type_name}">
                            </div>
                        `;
                    }
                    
                    // Build room card
                    const roomCard = `
                        <div class="room-card available-room" data-room-id="${room.id}">
                            <div class="room-slider">
                                ${sliderHTML}
                                <div class="room-price-badge">€${room.price_per_night}/natë</div>
                            </div>
                            
                            <div class="room-info">
                                <h3>${room.room_type_name}</h3>
                                <p class="room-number">Dhomë Nr. ${room.room_number}</p>
                                
                                <div class="room-features">
                                    <span class="feature">
                                        <i class="fas fa-users"></i>
                                        ${room.max_guests} mysafirë
                                    </span>
                                    <span class="feature">
                                        <i class="fas fa-moon"></i>
                                        ${room.nights} ${room.nights === 1 ? 'natë' : 'net'}
                                    </span>
                                </div>
                                
                                <p class="room-description">${room.description || ''}</p>
                                
                                <div class="room-amenities">
                                    ${room.amenities && room.amenities.length > 0 ? 
                                        room.amenities.slice(0, 4).map(amenity => 
                                            `<span class="amenity-tag">${amenity}</span>`
                                        ).join('') : ''}
                                    ${room.amenities && room.amenities.length > 4 ? 
                                        `<span class="amenity-tag">+${room.amenities.length - 4} më shumë</span>` : ''}
                                </div>
                                
                                <div class="room-details" style="background: #f8f9fa; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                                    <p style="font-size: 0.95rem; color: #666; margin-bottom: 0.5rem;">
                                        <strong>Çmimi për natë:</strong> €${room.price_per_night}
                                    </p>
                                    <p style="font-size: 1.1rem; font-weight: 600; color: var(--primary-color);">
                                        <strong>Totali për ${room.nights} ${room.nights === 1 ? 'natë' : 'net'}:</strong> €${room.total_price}
                                    </p>
                                </div>
                                
                                <button class="btn btn-primary" 
                                        onclick="window.bookingManager.openBookingModal(${room.id}, '${searchData.check_in}', '${searchData.check_out}', ${searchData.guests})">
                                    <i class="fas fa-calendar-check"></i> Rezervo Tani
                                </button>
                            </div>
                        </div>
                    `;
                    
                    availableRooms.insertAdjacentHTML('beforeend', roomCard);
                });
            });
        }

        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    async getRoomPhotos(roomId) {
        try {
            const response = await fetch(`../api/get_room_photos.php?room_id=${roomId}`);
            const data = await response.json();
            return data.photos || [];
        } catch (error) {
            console.error('Error fetching room photos:', error);
            return [];
        }
    }

    openBookingModal(roomId, checkIn, checkOut, guests) {
        // Find room details
        const roomCard = document.querySelector(`.available-room[data-room-id="${roomId}"]`);
        if (!roomCard) return;

        const roomName = roomCard.querySelector('h3').textContent;
        const roomNumber = roomCard.querySelector('.room-number').textContent;
        const pricePerNight = roomCard.querySelector('.room-price-badge').textContent;
        const totalPriceElement = roomCard.querySelector('.room-details p:last-child');
        const totalPrice = totalPriceElement ? totalPriceElement.textContent : '';

        // Set form values
        document.getElementById('selected_room_id').value = roomId;
        document.getElementById('selected_check_in').value = checkIn;
        document.getElementById('selected_check_out').value = checkOut;
        document.getElementById('selected_guests').value = guests;

        // Update summary
        document.getElementById('bookingSummary').innerHTML = `
            <div style="margin-bottom: 1rem;">
                <h4 style="color: var(--primary-color); margin-bottom: 0.5rem;">${roomName}</h4>
                <p style="color: #666;">${roomNumber}</p>
            </div>
            <div style="margin-bottom: 1rem;">
                <p style="margin-bottom: 0.5rem;"><strong>Check-in:</strong> ${checkIn}</p>
                <p style="margin-bottom: 0.5rem;"><strong>Check-out:</strong> ${checkOut}</p>
                <p style="margin-bottom: 0.5rem;"><strong>Mysafirë:</strong> ${guests}</p>
                <p style="margin-bottom: 0.5rem;"><strong>Çmimi:</strong> ${pricePerNight}</p>
            </div>
            <div style="padding-top: 1rem; border-top: 2px solid var(--secondary-color);">
                <h4 style="color: var(--primary-color);">${totalPrice}</h4>
            </div>
        `;

        // Show modal
        document.getElementById('bookingModal').style.display = 'block';
    }

    closeBookingModal() {
        document.getElementById('bookingModal').style.display = 'none';
        document.getElementById('bookingForm').reset();
    }

    processBooking() {
        const formData = new FormData(document.getElementById('bookingForm'));
        const data = {
            room_id: formData.get('room_id'),
            check_in: formData.get('check_in'),
            check_out: formData.get('check_out'),
            guests: formData.get('guests'),
            user_id: window.currentUserId || 0,
            special_requests: formData.get('special_requests'),
            payment_method: formData.get('payment_method')
        };

        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Duke procesuar...';
        submitBtn.disabled = true;

        fetch('../api/process_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                alert('Rezervimi u konfirmua! ID e rezervimit tuaj është: ' + result.reservation_id);
                this.closeBookingModal();
                window.location.href = 'my_reservations.php';
            } else {
                alert('Gabim: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ndodhi një gabim gjatë procesimit të rezervimit');
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    }
}

// Global function for modal close
function closeBookingModal() {
    if (window.bookingManager) {
        window.bookingManager.closeBookingModal();
    }
}