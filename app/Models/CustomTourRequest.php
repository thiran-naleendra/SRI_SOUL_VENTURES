<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CustomTourRequest extends DomainModel
{
    protected function casts(): array
    {
        return ['arrival_date' => 'date', 'departure_date' => 'date', 'budget_min' => 'decimal:2', 'budget_max' => 'decimal:2', 'contacted_at' => 'datetime', 'quotation_sent_at' => 'datetime', 'confirmed_at' => 'datetime'];
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function destinations(): BelongsToMany
    {
        return $this->belongsToMany(Destination::class);
    }

    public function travelStyles(): BelongsToMany
    {
        return $this->belongsToMany(TravelStyle::class);
    }
}
