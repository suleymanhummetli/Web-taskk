const noteInput = document.getElementById('noteInput');
const addBtn = document.getElementById('addBtn');
const noteList = document.getElementById('noteList');

// 1. Bazadan (LocalStorage) məlumatları yüklə
let notes = JSON.parse(localStorage.getItem('my_notes')) || [];

// 2. Səhifə açılanda siyahını göstər
renderNotes();

// 3. Əlavə etmə funksiyası
addBtn.addEventListener('click', () => {
    const text = noteInput.value.trim();
    if (text !== "") {
        notes.push(text); // Massivə əlavə et
        updateStorage();   // Bazanı yenilə
        renderNotes();    // Ekranda göstər
        noteInput.value = ""; // İnputu təmizlə
    }
});

// 4. Ekranda siyahını yaratmaq
function renderNotes() {
    noteList.innerHTML = ""; // Köhnə siyahını təmizlə
    notes.forEach((note, index) => {
        const li = document.createElement('li');
        li.innerHTML = `
            <span>${note}</span>
            <button class="delete-btn" onclick="deleteNote(${index})">Sil</button>
        `;
        noteList.appendChild(li);
    });
}

// 5. Silmə funksiyası
function deleteNote(index) {
    notes.splice(index, 1); // Massivdən həmin indeksi sil
    updateStorage();        // Bazanı yenilə
    renderNotes();         // Ekranı yenilə
}

// 6. LocalStorage-ə yazma funksiyası
function updateStorage() {
    localStorage.setItem('my_notes', JSON.stringify(notes));
}