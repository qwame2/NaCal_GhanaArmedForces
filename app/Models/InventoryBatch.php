<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

use App\Models\User;
use App\Models\Message;

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
        'delivery_phone',
        'auditor_status',
        'auditor_approved_by',
        'auditor_approved_at',
        'admin_status',
        'admin_approved_by',
        'admin_approved_at',
        'stores_approved_by',
        'stores_approved_at'
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

    public function auditorApprover()
    {
        return $this->belongsTo(User::class, 'auditor_approved_by');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function storesApprover()
    {
        return $this->belongsTo(User::class, 'stores_approved_by');
    }

    public static function sendSraReviewNotifications(InventoryBatch $batch)
    {
        $reviewers = User::whereIn('role', ['Auditor', 'Main Admin'])->where('is_active', true)->get();
        $supplierName = trim(preg_replace('/\[.*?\]/', '', ($batch->acquisition_type === 'Donor' ? ($batch->donor_name ?: $batch->supplier_name) : $batch->supplier_name) ?? 'N/A'));
        $reviewUrl = route('receiveditems.sra', ['id' => $batch->id]);
        
        $msgHtml = "<div class='sra-review-card' style='padding: 18px; border: 1px solid #4f46e5; border-radius: 16px; background: rgba(79, 70, 229, 0.03); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.08);'>";
        $msgHtml .= "<div style='display: flex; align-items: center; gap: 10px; margin-bottom: 12px;'>";
        $msgHtml .= "  <div style='width: 36px; height: 36px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; border-radius: 10px; display: flex; align-items: center; justify-content: center;'><i data-lucide='shield-alert' style='width:18px;height:18px;'></i></div>";
        $msgHtml .= "  <div>";
        $msgHtml .= "    <b style='color: #4f46e5; font-size: 0.95rem; display: block;'>SRA Review Pending</b>";
        $msgHtml .= "    <span style='font-size: 0.72rem; color: #64748b;'>SRA # " . str_pad($batch->id, 6, '0', STR_PAD_LEFT) . "</span>";
        $msgHtml .= "  </div>";
        $msgHtml .= "</div>";
        $msgHtml .= "<p style='font-size: 0.85rem; color: #334155; line-height: 1.5; margin: 0 0 14px 0;'>A new stock entry from <b>{$supplierName}</b> has been registered and is pending your verification and signature approval.</p>";
        $msgHtml .= "<a href='{$reviewUrl}' class='btn' style='display: block; text-align: center; background: #4f46e5; color: white; text-decoration: none; padding: 12px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15); transition: 0.2s;'>Review SRA Receipt</a>";
        $msgHtml .= "</div>";

        foreach ($reviewers as $reviewer) {
            Message::create([
                'sender_id' => auth()->id() ?: User::where('role', 'Head of Stores')->first()?->id ?: 1,
                'receiver_id' => $reviewer->id,
                'message' => $msgHtml,
                'is_automated' => true,
                'read_at' => null,
            ]);
        }
    }
}
