class Application {
    constructor() {
        this.notesContainer = document.querySelector('.notes-container');
        this.form = document.querySelector('form');
        this.contentInput = document.querySelector('textarea[name="content"]');
        this.apiEndpoint = '/api/notes';

        this.form.addEventListener('submit', (event) => this.postNote(event));
        document.addEventListener('DOMContentLoaded', () => this.getNotes());
    }

    async getNotes() {
        try {
            const response = await fetch(this.apiEndpoint);
            if (!response.ok) {
                throw new Error('Failed to fetch notes');
            }

            const notes = await response.json();
            this.notesContainer.innerHTML = '';

            notes.forEach(note => {
                const noteElement = document.createElement('div');
                noteElement.classList.add('note');


                let p = document.createElement('p');
                p.textContent = note.content;
                noteElement.appendChild(p);

                let small = document.createElement('small');
                small.textContent = `Created at: ${note.created_at}`;
                noteElement.appendChild(small);

                this.notesContainer.appendChild(noteElement);
            });
        } catch (error) {
            console.error('Error loading notes:', error);
        }
    }

    async postNote(event) {
        event.preventDefault();

        const content = this.contentInput.value.trim();

        if (content === '') {
            alert('Please enter content for the note.');
            return;
        }

        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ content })
            });

            if (response.ok) {
                const data = await response.json();

                this.contentInput.value = '';

                if (data.response === "success") {
                    const note = data.note;

                    const newNote = document.createElement('div');
                    newNote.classList.add('note');
                    let p = document.createElement('p');
                    p.textContent = note.content;
                    newNote.appendChild(p);

                    let small = document.createElement('small');
                    small.textContent = `Created at: ${note.created_at}`;
                    newNote.appendChild(small);

                    this.notesContainer.insertBefore(newNote, this.notesContainer.firstChild);
                }
            } else {
                const errorData = await response.json();
                alert(`Error: ${errorData.message || 'Unable to create note'}`);
            }
        } catch (error) {
            console.error('Error sending the request:', error);
            alert('Something went wrong. Please try again.');
        }
    }
}

new Application();
