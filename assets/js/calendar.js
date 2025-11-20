// Calendar functionality
class HotelCalendar {
    constructor() {
        this.currentCalendar = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Room filter
        const roomFilter = document.getElementById('roomFilter');
        if (roomFilter) {
            roomFilter.addEventListener('change', () => {
                this.refreshCalendar();
            });
        }

        // Source filter
        const sourceFilter = document.getElementById('sourceFilter');
        if (sourceFilter) {
            sourceFilter.addEventListener('change', () => {
                this.refreshCalendar();
            });
        }
    }

    refreshCalendar() {
        if (this.currentCalendar) {
            this.currentCalendar.refetchEvents();
        }
    }

    // Get calendar events with filters
    getCalendarEvents(fetchInfo, successCallback, failureCallback) {
        const roomId = document.getElementById('roomFilter')?.value || '';
        const source = document.getElementById('sourceFilter')?.value || '';

        const params = new URLSearchParams({
            start: fetchInfo.startStr,
            end: fetchInfo.endStr,
            room_id: roomId,
            source: source
        });

        fetch(`../api/calendar_events.php?${params}`)
            .then(response => response.json())
            .then(data => {
                successCallback(data);
            })
            .catch(error => {
                console.error('Error fetching calendar events:', error);
                failureCallback(error);
            });
    }
}

// Initialize calendar when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.hotelCalendar = new HotelCalendar();
});

// Utility functions for calendar
function formatDateForCalendar(date) {
    return date.toISOString().split('T')[0];
}

function getEventColor(source, status) {
    if (status === 'maintenance') return '#f39c12';
    if (status === 'checked_in') return '#27ae60';
    if (source === 'booking_com') return '#e74c3c';
    return '#3498db';
}