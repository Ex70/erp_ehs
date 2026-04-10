<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class TicketAsignacion extends Model
{
    protected $table    = 'ticket_asignaciones';
    protected $fillable = ['ticket_id', 'user_id', 'activo'];
    protected $casts    = ['activo' => 'boolean'];

    public function ticket() { return $this->belongsTo(Ticket::class); }
    public function tecnico() { return $this->belongsTo(User::class, 'user_id'); }
}