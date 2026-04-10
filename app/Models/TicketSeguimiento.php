<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TicketSeguimiento extends Model
{
    protected $table    = 'ticket_seguimientos';
    protected $fillable = ['ticket_id', 'user_id', 'estado', 'comentario'];

    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function usuario() { return $this->belongsTo(User::class, 'user_id'); }
}