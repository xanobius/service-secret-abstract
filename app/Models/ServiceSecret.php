<?php


use App\Enums\Services;
use App\Enums\ServiceEnvs;
use App\Enums\Tenants;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServiceSecret extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'service' => Services::class,
            'tenants' => Tenants::class,
            'env' => ServiceEnvs::class,
            'secrets' => '<encrypted:json>'
        ];
    }

    public function stuffsWithSecrets () : BelongsToMany
    {
        return $this->belongsToMany(StuffWithSecret::class);
    }
}
