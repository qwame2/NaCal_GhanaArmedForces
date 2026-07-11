<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected static function booted()
    {
        static::saved(function () {
            \Illuminate\Support\Facades\Cache::forget('global_recent_system_logs');
        });
    }

    protected $fillable = [
        'user_id',
        'event_type',
        'action',
        'description',
        'severity',
        'metadata',
        'ip_address',
        'is_archived'
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_archived' => 'boolean'
    ];

    public function getFriendlyDescriptionAttribute()
    {
        $desc = $this->description;
        $desc = str_replace(['Main Admin', 'Head of Admin'], 'Head of Admin(Authorizer)', $desc);
        $desc = str_replace('Head of Admin(Authorizer)(Authorizer)', 'Head of Admin(Authorizer)', $desc);
        $desc = str_replace('submitted an update to', 'updated', $desc);
        $desc = str_replace('removed an entry from', 'deleted an item from', $desc);
        $desc = str_replace('modified records in', 'modified', $desc);
        $desc = str_replace('updated the Admin Hub', 'updated the system settings', $desc);
        $desc = str_replace('modified the Admin Hub', 'modified the system settings', $desc);
        $desc = str_replace('interacted with the Admin Hub', 'updated the system settings', $desc);
        
        if (preg_match('#(?:updated|modified|interacted with) the (Stores|Admin|Personnel) Service Sra (\d+) Process page#i', $desc, $matches)) {
            $sraId = $matches[2];
            $sra = \App\Models\ServiceSra::find($sraId);
            $sraNum = $sra ? $sra->sra_number : "SRA-" . str_pad($sraId, 6, '0', STR_PAD_LEFT);
            $actor = 'Head of Stores';
            if (str_starts_with($desc, 'Head of Admin')) {
                $actor = 'Head of Admin(Authorizer)';
            }
            return "{$actor} processed Service SRA {$sraNum} (the document confirming that new items were received into the store).";
        }
        
        $reqId = null;
        if ($this->metadata && isset($this->metadata['requisition_id'])) {
            $reqId = $this->metadata['requisition_id'];
        } else {
            if (preg_match('/requisition\s+#(\d+)/i', $desc, $matches)) {
                $reqId = $matches[1];
            }
        }

        if ($reqId) {
            $requisition = \App\Models\StoreRequisition::with('items')->find($reqId);
            if ($requisition && $requisition->items->isNotEmpty()) {
                $itemStrings = [];
                foreach ($requisition->items as $item) {
                    $qty = $item->quantity_approved ?: $item->quantity_requested;
                    $unit = $item->unit ? " " . $item->unit : "";
                    $itemStrings[] = "{$qty}{$unit} of " . ($item->description ?: 'Unnamed Item');
                }
                $itemsList = implode(', ', $itemStrings);
                
                $newDesc = preg_replace('/store requisition\s+#\d+/i', "a requisition for {$itemsList}", $desc);
                $newDesc = preg_replace('/requisition\s+#\d+/i', "a requisition for {$itemsList}", $newDesc);
                $newDesc = str_replace("from department: ", "from the ", $newDesc);
                $newDesc = str_replace("from department ", "from the ", $newDesc);
                return $newDesc;
            }
        }

        return $desc;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
