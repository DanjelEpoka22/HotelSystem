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
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Checking...';
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
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking availability');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }

    displayAvailableRooms(rooms, searchData) {
        const resultsSection = document.getElementById('resultsSection');
        const availableRooms = document.getElementById('availableRooms');

        if (rooms.length === 0) {
            availableRooms.innerHTML = `
                <div class="no-rooms">
                    <p>No rooms available for the selected dates and criteria.</p>
                    <p>Please try different dates or adjust your search criteria.</p>
                </div>
            `;
        } else {
            let roomsHTML = '';
            
            rooms.forEach(room => {
                roomsHTML += `
                    <div class="room-card available-room" data-room-id="${room.id}">
                        <div class="room-image">
                            <img src="../assets/images/rooms/${room.id}/1.jpg" alt="${room.room_type_name}" 
                                 onerror="this.src='../assets/images/rooms/default.jpg'">
                        </div>
                        <div class="room-info">
                            <h3>${room.room_type_name}</h3>
                            <p class="room-number">Room ${room.room_number}</p>
                            <p class="room-description">${room.description}</p>
                            
                            <div class="room-details">
                                <p><strong>Max Guests:</strong> ${room.max_guests}</p>
                                <p><strong>Price per Night:</strong> €${room.price_per_night}</p>
                                <p><strong>Total for ${room.nights} nights:</strong> €${room.total_price}</p>
                            </div>
                            
                            <div class="room-amenities">
                                ${room.amenities ? room.amenities.map(amenity => 
                                    `<span class="amenity-tag">${amenity}</span>`
                                ).join('') : ''}
                            </div>
                            
                            <button class="btn btn-primary book-room-btn" 
                                    onclick="window.bookingManager.openBookingModal(${room.id}, '${searchData.check_in}', '${searchData.check_out}', ${searchData.guests})">
                                Book Now
                            </button>
                        </div>
                    </div>
                `;
            });

            availableRooms.innerHTML = roomsHTML;
        }

        resultsSection.style.display = 'block';
        resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    openBookingModal(roomId, checkIn, checkOut, guests) {
        // Find room details
        const roomCard = document.querySelector(`.available-room[data-room-id="${roomId}"]`);
        if (!roomCard) return;

        const roomName = roomCard.querySelector('h3').textContent;
        const roomNumber = roomCard.querySelector('.room-number').textContent;
        const totalPrice = roomCard.querySelector('.room-details p:last-child').textContent;

        // Set form values
        document.getElementById('selected_room_id').value = roomId;
        document.getElementById('selected_check_in').value = checkIn;
        document.getElementById('selected_check_out').value = checkOut;
        document.getElementById('selected_guests').value = guests;

        // Update summary
        document.getElementById('bookingSummary').innerHTML = `
            <div class="summary-item">
                <h4>${roomName}</h4>
                <p>${roomNumber}</p>
            </div>
            <div class="summary-item">
                <p><strong>Check-in:</strong> ${checkIn}</p>
                <p><strong>Check-out:</strong> ${checkOut}</p>
                <p><strong>Guests:</strong> ${guests}</p>
            </div>
            <div class="summary-item">
                <h4>${totalPrice}</h4>
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
            user_id: window.currentUserId || 0, // Do të vendoset nga PHP
            special_requests: formData.get('special_requests'),
            payment_method: formData.get('payment_method')
        };

        const submitBtn = document.querySelector('#bookingForm button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Processing...';
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
                alert('Booking confirmed! Your reservation ID is: ' + result.reservation_id);
                this.closeBookingModal();
                window.location.href = 'my_reservations.php';
            } else {
                alert('Error: ' + result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while processing your booking');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
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