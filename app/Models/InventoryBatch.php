<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class InventoryBatch extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::saved(function () {
            Setting::clearInventoryCache();
        });
        static::deleted(function () {
            Setting::clearInventoryCache();
        });
    }

    public static function selfHealSchema()
    {
        $cacheKey = 'inventory_batch_schema_self_healed';
        if (\Illuminate\Support\Facades\Cache::has($cacheKey)) {
            return;
        }

        $success = true;

        if (Schema::hasTable('suppliers') && !Schema::hasColumn('suppliers', 'delivery_phone')) {
            try {
                Schema::table('suppliers', function (Blueprint $table) {
                    $table->string('delivery_phone')->nullable()->after('delivery_person');
                });
            } catch (\Exception $e) {
                $success = false;
                // Ignore concurrent schema updates or errors
            }
        }

        if (Schema::hasTable('suppliers')) {
            $hasContactPerson = Schema::hasColumn('suppliers', 'contact_person');
            $hasContactPhone = Schema::hasColumn('suppliers', 'contact_phone');
            if (!$hasContactPerson || !$hasContactPhone) {
                try {
                    Schema::table('suppliers', function (Blueprint $table) use ($hasContactPerson, $hasContactPhone) {
                        if (!$hasContactPerson) {
                            $table->string('contact_person')->nullable()->after('name');
                        }
                        if (!$hasContactPhone) {
                            $table->string('contact_phone')->nullable()->after($hasContactPerson ? 'contact_person' : 'name');
                        }
                    });
                } catch (\Exception $e) {
                    $success = false;
                    // Ignore concurrent schema updates or errors
                }
            }
        }

        if (Schema::hasTable('inventory_batches')) {
            $hasPerson = Schema::hasColumn('inventory_batches', 'delivery_person');
            $hasPhone = Schema::hasColumn('inventory_batches', 'delivery_phone');
            if (!$hasPerson || !$hasPhone) {
                try {
                    Schema::table('inventory_batches', function (Blueprint $table) use ($hasPerson, $hasPhone) {
                        if (!$hasPerson) {
                            $table->string('delivery_person')->nullable()->after('arrival_date');
                        }
                        if (!$hasPhone) {
                            $table->string('delivery_phone')->nullable()->after($hasPerson ? 'delivery_person' : 'arrival_date');
                        }
                    });
                } catch (\Exception $e) {
                    $success = false;
                    // Ignore concurrent schema updates or errors
                }
            }
        }

        // Cache the successful validation for 7 days if all schema modifications succeeded
        if ($success) {
            \Illuminate\Support\Facades\Cache::put($cacheKey, true, 604800);
        }
    }

    protected $fillable = [
        'ledge_category',
        'supplier_name',
        'donor_name',
        'acquisition_type',
        'supplier_status',
        'entry_date',
        'arrival_date',
        'recorded_by',
        'approval_status',
        'approved_by',
        'approved_at',
        'delivery_person',
        'delivery_phone'
    ];

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'batch_id');
    }

    public function recorder()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
