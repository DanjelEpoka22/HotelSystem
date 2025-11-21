let currentRoomId = null;
let selectedFiles = [];

function managePhotos(roomId, roomNumber) {
    currentRoomId = roomId;
    document.getElementById('modalRoomNumber').textContent = roomNumber;
    document.getElementById('photoModal').style.display = 'block';
    loadCurrentPhotos(roomId);
    setupUploadArea();
}

function closePhotoModal() {
    document.getElementById('photoModal').style.display = 'none';
    selectedFiles = [];
    document.getElementById('previewContainer').innerHTML = '';
    document.getElementById('previewContainer').style.display = 'none';
    document.getElementById('uploadBtn').style.display = 'none';
}

function setupUploadArea() {
    const uploadArea = document.getElementById('uploadArea');
    const photoInput = document.getElementById('photoInput');
    
    // Click to upload
    uploadArea.onclick = () => photoInput.click();
    
    // File input change
    photoInput.onchange = (e) => handleFiles(e.target.files);
    
    // Drag and drop
    uploadArea.ondragover = (e) => {
        e.preventDefault();
        uploadArea.classList.add('dragover');
    };
    
    uploadArea.ondragleave = () => {
        uploadArea.classList.remove('dragover');
    };
    
    uploadArea.ondrop = (e) => {
        e.preventDefault();
        uploadArea.classList.remove('dragover');
        handleFiles(e.dataTransfer.files);
    };
}

function handleFiles(files) {
    selectedFiles = Array.from(files);
    displayPreview();
}

function displayPreview() {
    const container = document.getElementById('previewContainer');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (selectedFiles.length === 0) {
        container.style.display = 'none';
        uploadBtn.style.display = 'none';
        return;
    }
    
    container.innerHTML = '';
    container.style.display = 'grid';
    uploadBtn.style.display = 'block';
    
    selectedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'preview-item';
            div.innerHTML = `
                <img src="${e.target.result}" alt="Preview">
                <button class="remove-preview" onclick="removePreview(${index})">×</button>
            `;
            container.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
    
    uploadBtn.onclick = uploadPhotos;
}

function removePreview(index) {
    selectedFiles.splice(index, 1);
    displayPreview();
}

function uploadPhotos() {
    if (selectedFiles.length === 0) return;
    
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('room_id', currentRoomId);
    
    selectedFiles.forEach((file, index) => {
        formData.append('photos[]', file);
    });
    
    const uploadBtn = document.getElementById('uploadBtn');
    uploadBtn.disabled = true;
    uploadBtn.textContent = 'Uploading...';
    
    fetch('ajax/photo_actions.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Successfully uploaded ${data.uploaded} photo(s)!`);
            selectedFiles = [];
            document.getElementById('previewContainer').innerHTML = '';
            document.getElementById('previewContainer').style.display = 'none';
            document.getElementById('uploadBtn').style.display = 'none';
            document.getElementById('photoInput').value = '';
            loadCurrentPhotos(currentRoomId);
        } else {
            alert('Error uploading photos: ' + data.error);
        }
        
        uploadBtn.disabled = false;
        uploadBtn.textContent = 'Upload Photos';
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while uploading photos');
        uploadBtn.disabled = false;
        uploadBtn.textContent = 'Upload Photos';
    });
}

function loadCurrentPhotos(roomId) {
    fetch(`ajax/photo_actions.php?action=get_photos&room_id=${roomId}`)
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('currentPhotos');
        
        if (data.success && data.photos.length > 0) {
            container.innerHTML = '';
            data.photos.forEach(photo => {
                const div = document.createElement('div');
                div.className = 'photo-item';
                div.innerHTML = `
                    <img src="../assets/images/rooms/uploads/${photo.photo_filename}" alt="Room photo">
                    <div class="photo-actions">
                        <button class="delete-photo" onclick="deletePhoto(${photo.id})">Delete</button>
                    </div>
                `;
                container.appendChild(div);
            });
        } else {
            container.innerHTML = '<p style="color:#999;">No photos uploaded yet</p>';
        }
    })
    .catch(error => {
        console.error('Error loading photos:', error);
        document.getElementById('currentPhotos').innerHTML = '<p style="color:#e74c3c;">Error loading photos</p>';
    });
}

function deletePhoto(photoId) {
    if (!confirm('Are you sure you want to delete this photo?')) return;
    
    fetch(`ajax/photo_actions.php?action=delete&id=${photoId}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadCurrentPhotos(currentRoomId);
            // Reload page to update photo count
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error deleting photo: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the photo');
    });
}

// Close modal when clicking outside
window.onclick = (event) => {
    const modal = document.getElementById('photoModal');
    if (event.target === modal) {
        closePhotoModal();
    }
};