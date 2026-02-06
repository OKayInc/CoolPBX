<?php

namespace App\Livewire;

use App\Http\Requests\ContactNoteRequest;
use App\Models\Contact;
use App\Models\ContactNote;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ContactNoteForm extends Component
{
    public $contactUuid;
    public $notes = [];
    public $removedNotes = [];

    protected $listeners = [
        'notesSaved' => 'save'
    ];

    public function mount($contactUuid): void
    {
        $this->contactUuid = $contactUuid;
        $this->loadNotes();
    }

    public function rules(): array
    {
        $request = new ContactNoteRequest();
        return $request->rules();
    }

    public function loadNotes(): void
    {
        $contact = Contact::where('contact_uuid', $this->contactUuid)
            ->with(['notes' => function($query) {
                $query->orderBy('insert_date', 'desc');
            }])
            ->first();

        if ($contact && $contact->notes->count() > 0) {
            $this->notes = $contact->notes->map(function ($note) {
                return [
                    'contact_note_uuid' => $note->contact_note_uuid,
                    'contact_note' => $note->contact_note,
                    'insert_date' => $note->insert_date,
                    'insert_user' => $note->insert_user ?? 'Unknown',
                ];
            })->toArray();
        } else {
            $this->addNote();
        }
    }

    public function addNote()
    {
        if (!auth()->user()->hasPermission('contact_note_add')) {
            session()->flash('message', 'You do not have permission to add notes.');
            return;
        }

        $this->notes[] = [
            'contact_note_uuid' => null,
            'contact_note' => '',
            'insert_date' => null,
            'insert_user' => null,
        ];
    }

    public function removeNote($index)
    {
        if (!auth()->user()->hasPermission('contact_note_delete')) {
            session()->flash('message', 'You do not have permission to delete notes.');
            return;
        }

        if (isset($this->notes[$index]['contact_note_uuid']) && !empty($this->notes[$index]['insert_date'])) {
            $this->removedNotes[] = $this->notes[$index];
        }

        unset($this->notes[$index]);
        $this->notes = array_values($this->notes);
    }

    public function save()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            foreach ($this->removedNotes as $removedNote) {
                if (isset($removedNote['contact_note_uuid'])) {
                    ContactNote::where('contact_note_uuid', $removedNote['contact_note_uuid'])->delete();
                }
            }

            foreach ($this->notes as $note) {
                if (!empty($note['contact_note'])) {
                    if (isset($note['contact_note_uuid']) && !empty($note['contact_note_uuid'])) {
                        $contactNote = ContactNote::where('contact_note_uuid', $note['contact_note_uuid'])->first();
                        if ($contactNote) {
                            $contactNote->contact_note = $note['contact_note'];
                            $contactNote->update_user = auth()->user()->user_uuid;
                            $contactNote->save();
                        }
                    } else {
                        $contactNote = new ContactNote();
                        $contactNote->contact_uuid = $this->contactUuid;
                        $contactNote->domain_uuid = Session::get('domain_uuid');
                        $contactNote->contact_note = $note['contact_note'];
                        $contactNote->insert_user = auth()->user()->user_uuid;
                        $contactNote->update_user = auth()->user()->user_uuid;
                        $contactNote->save();
                    }
                }
            }

            DB::commit();

            $this->loadNotes();

            $this->removedNotes = [];

            $this->dispatch('timesSaved')->to(ContactTimeForm::class);

        } catch (\Throwable $e) {
            DB::rollBack();
            session()->flash('message', 'Error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function render()
    {
        return view('livewire.contact-note-form');
    }
}