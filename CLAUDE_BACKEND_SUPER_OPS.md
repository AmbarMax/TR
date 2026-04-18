# CLAUDE_BACKEND_SUPER_OPS.md — Backend Superbrief: Rewards + Virtual Hall

> Read TROPHYROOM_WORKING_GUIDE.md and CLAUDE.md before starting.
> Execute ONE step at a time. Run `php artisan` or verification commands after each step.
> This brief includes migrations — run them on the SERVER after deploy, NOT locally.
> Do NOT touch any frontend files except VirtualHall.vue (Step 9).

---

## Overview

This brief covers ALL remaining backend work to connect the frontend pages we built:

- **Steps 1-4:** Rewards backend (Battle Pass, Ambar→Uru conversion, Shop, Chests without Web3)
- **Steps 5-7:** Virtual Hall backend (social links, featured slots, update endpoint)
- **Step 8:** Connect frontend Rewards.vue to real endpoints
- **Step 9:** VirtualHall.vue UI tweak (Copy Link → Edit VHall + copy icon)
- **Step 10:** Connect frontend Settings (Profile.vue) Virtual Hall section to real endpoints

---

## Step 1 — Migration: add social links + featured to users table, create battle_pass_levels and shop_items tables

**Create migration file.** Run:

```bash
php artisan make:migration add_rewards_and_vhall_fields --create=battle_pass_levels
```

Then **replace the ENTIRE migration file content** with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Social links + featured slots on users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('social_twitter')->nullable()->after('description');
            $table->string('social_twitch')->nullable()->after('social_twitter');
            $table->string('social_youtube')->nullable()->after('social_twitch');
            $table->string('social_instagram')->nullable()->after('social_youtube');
            $table->string('social_discord_tag')->nullable()->after('social_instagram');
            $table->string('social_website')->nullable()->after('social_discord_tag');
            $table->json('featured_slots')->nullable()->after('social_website');
        });

        // Battle pass levels
        Schema::create('battle_pass_levels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->integer('number')->unique();
            $table->string('name');
            $table->integer('cost_uru')->default(0);
            $table->json('rewards')->nullable(); // [{"type":"ambar","amount":50},{"type":"key","key_id":"uuid","quantity":1}]
            $table->timestamps();
        });

        // User battle pass progress
        Schema::create('battle_pass_user', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('level_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('level_id')->references('id')->on('battle_pass_levels')->onDelete('cascade');
            $table->unique(['user_id', 'level_id']);
        });

        // Shop items (managed by TrophyRoom team only)
        Schema::create('shop_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('image')->nullable();
            $table->text('description')->nullable();
            $table->integer('price_uru');
            $table->integer('stock')->default(-1); // -1 = unlimited
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Shop purchases
        Schema::create('shop_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('shop_item_id');
            $table->integer('price_paid');
            $table->string('status')->default('pending'); // pending, delivered, cancelled
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shop_item_id')->references('id')->on('shop_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_purchases');
        Schema::dropIfExists('shop_items');
        Schema::dropIfExists('battle_pass_user');
        Schema::dropIfExists('battle_pass_levels');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'social_twitter', 'social_twitch', 'social_youtube',
                'social_instagram', 'social_discord_tag', 'social_website',
                'featured_slots'
            ]);
        });
    }
};
```

**Verify:** `php artisan migrate --pretend` — should show the SQL without executing.

---

## Step 2 — Create Models: BattlePassLevel, ShopItem, ShopPurchase

**Create file:** `app/Models/BattlePassLevel.php`

```php
<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BattlePassLevel extends Model
{
    use HasFactory, UUID;

    protected $fillable = ['number', 'name', 'cost_uru', 'rewards'];

    protected $casts = [
        'rewards' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'battle_pass_user')
            ->withTimestamps();
    }
}
```

**Create file:** `app/Models/ShopItem.php`

```php
<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopItem extends Model
{
    use HasFactory, UUID;

    protected $fillable = ['name', 'image', 'description', 'price_uru', 'stock', 'is_active'];

    public function purchases()
    {
        return $this->hasMany(ShopPurchase::class);
    }
}
```

**Create file:** `app/Models/ShopPurchase.php`

```php
<?php

namespace App\Models;

use App\Http\Traits\UUID;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShopPurchase extends Model
{
    use HasFactory, UUID;

    protected $fillable = ['user_id', 'shop_item_id', 'price_paid', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(ShopItem::class, 'shop_item_id');
    }
}
```

**Update `app/Models/User.php`** — add these fields to the `$fillable` array:

```php
'social_twitter',
'social_twitch',
'social_youtube',
'social_instagram',
'social_discord_tag',
'social_website',
'featured_slots',
```

Also add to the `$casts` array:

```php
'featured_slots' => 'array',
```

Also add these relationships at the end of the User model class:

```php
public function battlePassLevels()
{
    return $this->belongsToMany(BattlePassLevel::class, 'battle_pass_user')
        ->withTimestamps();
}

public function shopPurchases()
{
    return $this->hasMany(ShopPurchase::class);
}
```

**Verify:** `php artisan tinker` → `new \App\Models\BattlePassLevel;` — should not error.

---

## Step 3 — Create RewardsService

**Create file:** `app/Services/Api/RewardsService.php`

```php
<?php

namespace App\Services\Api;

use App\Models\BattlePassLevel;
use App\Models\ShopItem;
use App\Models\ShopPurchase;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RewardsService
{
    public function __construct(private readonly BalanceService $balanceService)
    {
    }

    /**
     * Get battle pass data for current user
     */
    public function getBattlePass()
    {
        $levels = BattlePassLevel::orderBy('number')->get();
        $ownedIds = Auth::user()->battlePassLevels()->pluck('battle_pass_levels.id')->toArray();

        return $levels->map(function ($level) use ($ownedIds) {
            $owned = in_array($level->id, $ownedIds);
            return [
                'id' => $level->id,
                'number' => $level->number,
                'name' => $level->name,
                'cost_uru' => $level->cost_uru,
                'rewards' => $level->rewards,
                'owned' => $owned,
            ];
        });
    }

    /**
     * Buy the next battle pass level
     */
    public function buyLevel($levelId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $level = BattlePassLevel::findOrFail($levelId);

            // Check user doesn't already own this level
            if ($user->battlePassLevels()->where('battle_pass_levels.id', $levelId)->exists()) {
                throw new Exception('Level already owned');
            }

            // Check all previous levels are owned
            $previousLevels = BattlePassLevel::where('number', '<', $level->number)->get();
            foreach ($previousLevels as $prev) {
                if (!$user->battlePassLevels()->where('battle_pass_levels.id', $prev->id)->exists()) {
                    throw new Exception('Must own all previous levels first');
                }
            }

            // Check user has enough Uru
            $uruBalance = $user->balances()->where('currency_id', 'uru')->first();
            if (!$uruBalance || $uruBalance->amount < $level->cost_uru) {
                throw new Exception('Insufficient Uru balance');
            }

            // Deduct Uru
            $this->balanceService->decreaseBalance($user->id, 'uru', $level->cost_uru);

            // Grant level
            $user->battlePassLevels()->attach($levelId);

            // Process rewards
            if ($level->rewards) {
                foreach ($level->rewards as $reward) {
                    if ($reward['type'] === 'ambar') {
                        $this->balanceService->increaseBalance($user->id, 'ambar', $reward['amount']);
                    }
                    if ($reward['type'] === 'key' && isset($reward['key_id'])) {
                        DB::table('key_user')->insert([
                            'user_id' => $user->id,
                            'key_id' => $reward['key_id'],
                            'token_id' => null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('BuyLevel error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert Ambar to Uru (rate: 10 Ambar = 1 Uru)
     */
    public function convertAmbarToUru($ambarAmount)
    {
        try {
            $user = Auth::user();

            if ($ambarAmount < 10) {
                throw new Exception('Minimum 10 Ambar required');
            }

            $ambarBalance = $user->balances()->where('currency_id', 'ambar')->first();
            if (!$ambarBalance || $ambarBalance->amount < $ambarAmount) {
                throw new Exception('Insufficient Ambar balance');
            }

            $uruAmount = floor($ambarAmount / 10);
            $actualAmbarSpent = $uruAmount * 10; // Only spend exact multiples of 10

            DB::beginTransaction();
            $this->balanceService->decreaseBalance($user->id, 'ambar', $actualAmbarSpent);
            $this->balanceService->increaseBalance($user->id, 'uru', $uruAmount);
            DB::commit();

            return [
                'ambar_spent' => $actualAmbarSpent,
                'uru_received' => $uruAmount,
            ];
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('ConvertAmbarToUru error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get active shop items
     */
    public function getShopItems()
    {
        return ShopItem::where('is_active', true)
            ->where(function ($q) {
                $q->where('stock', '>', 0)->orWhere('stock', -1);
            })
            ->get();
    }

    /**
     * Buy a shop item
     */
    public function buyShopItem($itemId)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $item = ShopItem::findOrFail($itemId);

            if (!$item->is_active) {
                throw new Exception('Item not available');
            }

            if ($item->stock !== -1 && $item->stock <= 0) {
                throw new Exception('Item out of stock');
            }

            $uruBalance = $user->balances()->where('currency_id', 'uru')->first();
            if (!$uruBalance || $uruBalance->amount < $item->price_uru) {
                throw new Exception('Insufficient Uru balance');
            }

            $this->balanceService->decreaseBalance($user->id, 'uru', $item->price_uru);

            if ($item->stock !== -1) {
                $item->stock--;
                $item->save();
            }

            ShopPurchase::create([
                'user_id' => $user->id,
                'shop_item_id' => $item->id,
                'price_paid' => $item->price_uru,
                'status' => 'pending',
            ]);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('BuyShopItem error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get purchase history for current user
     */
    public function getPurchaseHistory()
    {
        return ShopPurchase::with('item')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'name' => $purchase->item->name ?? 'Unknown item',
                    'price' => $purchase->price_paid,
                    'status' => $purchase->status,
                    'date' => $purchase->created_at->format('M d, Y'),
                ];
            });
    }
}
```

**Verify:** No artisan command needed — just ensure the file is saved correctly.

---

## Step 4 — Create RewardsController + routes

**Create file:** `app/Http/Controllers/Api/RewardsController.php`

```php
<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\RewardsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class RewardsController
{
    public function __construct(private readonly RewardsService $rewardsService)
    {
    }

    public function battlePass()
    {
        return response()->json([
            'levels' => $this->rewardsService->getBattlePass(),
        ], ResponseAlias::HTTP_OK);
    }

    public function buyLevel(Request $request)
    {
        $request->validate(['level_id' => 'required|string']);

        return $this->rewardsService->buyLevel($request->level_id)
            ? response()->json(['message' => 'Level purchased!'], ResponseAlias::HTTP_OK)
            : response()->json(['message' => 'Could not purchase level'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function convert(Request $request)
    {
        $request->validate(['ambar_amount' => 'required|integer|min:10']);

        $result = $this->rewardsService->convertAmbarToUru($request->ambar_amount);
        return $result
            ? response()->json($result, ResponseAlias::HTTP_OK)
            : response()->json(['message' => 'Conversion failed'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function shopItems()
    {
        return response()->json([
            'items' => $this->rewardsService->getShopItems(),
        ], ResponseAlias::HTTP_OK);
    }

    public function buyItem(Request $request)
    {
        $request->validate(['item_id' => 'required|string']);

        return $this->rewardsService->buyShopItem($request->item_id)
            ? response()->json(['message' => 'Item purchased!'], ResponseAlias::HTTP_OK)
            : response()->json(['message' => 'Could not purchase item'], ResponseAlias::HTTP_BAD_REQUEST);
    }

    public function purchaseHistory()
    {
        return response()->json([
            'purchases' => $this->rewardsService->getPurchaseHistory(),
        ], ResponseAlias::HTTP_OK);
    }
}
```

**Add routes to `routes/api.php`.** Find this block:

```php
    Route::prefix('exchange')
        ->name('exchange.')
```

Add the rewards routes BEFORE it (inside the JWT middleware group):

```php
    Route::prefix('rewards')
        ->name('rewards.')
        ->group(function () {
            Route::get('/battle-pass', [RewardsController::class, 'battlePass'])->name('battlePass');
            Route::post('/buy-level', [RewardsController::class, 'buyLevel'])->name('buyLevel');
            Route::post('/convert', [RewardsController::class, 'convert'])->name('convert');
            Route::get('/shop', [RewardsController::class, 'shopItems'])->name('shop');
            Route::post('/shop/buy', [RewardsController::class, 'buyItem'])->name('buyItem');
            Route::get('/shop/history', [RewardsController::class, 'purchaseHistory'])->name('purchaseHistory');
        });
```

Also add the import at the top of `routes/api.php`:

```php
use App\Http\Controllers\Api\RewardsController;
```

**Verify:** `php artisan route:list --path=rewards` — should show 6 routes.

NOTE: `php artisan route:list` may fail due to the known `badges.badges` duplicate route name issue. If it fails, verify with: `grep -c "rewards" routes/api.php` — should return at least 6.

---

## Step 5 — Update User model fillable + VirtualHall service methods

The User model fillable was already updated in Step 2. Now add a method to `app/Services/Api/UserService.php` for saving Virtual Hall data.

**Edit file:** `app/Services/Api/UserService.php`

Find the class and add these methods (at the end, before the closing `}`):

```php
    public function updateVirtualHall($userId, array $data)
    {
        try {
            $user = User::find($userId);
            if (!$user) return false;

            if (isset($data['social_twitter'])) $user->social_twitter = $data['social_twitter'];
            if (isset($data['social_twitch'])) $user->social_twitch = $data['social_twitch'];
            if (isset($data['social_youtube'])) $user->social_youtube = $data['social_youtube'];
            if (isset($data['social_instagram'])) $user->social_instagram = $data['social_instagram'];
            if (isset($data['social_discord_tag'])) $user->social_discord_tag = $data['social_discord_tag'];
            if (isset($data['social_website'])) $user->social_website = $data['social_website'];
            if (isset($data['featured_slots'])) $user->featured_slots = $data['featured_slots'];

            $user->save();
            return true;
        } catch (\Exception $e) {
            Log::error('UpdateVirtualHall error: ' . $e->getMessage());
            return false;
        }
    }
```

**Verify:** No artisan command needed.

---

## Step 6 — Add Virtual Hall endpoints to ProfileController

**Edit file:** `app/Http/Controllers/Api/User/ProfileController.php`

Add this method to the class:

```php
    public function updateVirtualHall(\Illuminate\Http\Request $request)
    {
        $data = $request->only([
            'social_twitter', 'social_twitch', 'social_youtube',
            'social_instagram', 'social_discord_tag', 'social_website',
            'featured_slots'
        ]);

        return $this->userService->updateVirtualHall(Auth::id(), $data)
            ? response()->json(['message' => 'Virtual Hall updated'], ResponseAlias::HTTP_CREATED)
            : response()->json(['message' => 'Virtual Hall not updated'], ResponseAlias::HTTP_BAD_REQUEST);
    }
```

**Add route to `routes/api.php`.** Find the profile routes section and add:

```php
    Route::put('/profile/update-virtual-hall', [ProfileController::class, 'updateVirtualHall']);
```

Add it near the other `/profile/*` routes.

**Verify:** `grep "update-virtual-hall" routes/api.php` — should return 1 line.

---

## Step 7 — Update VirtualHallController to return social links + featured

**Edit file:** `app/Http/Controllers/Api/User/VirtualHallController.php`

Replace the `show` method with:

```php
    public function show($username): JsonResponse
    {
        $userModel = $this->userService?->findByUserName($username);
        if (!$userModel) {
            return response()->json(['message' => 'User not found'], ResponseAlias::HTTP_NOT_FOUND);
        }

        $user = $userModel->getUserDataWithBalances();
        $user = UserResource::make($user)->response()->getData(true);

        $filteredTrophies = array_filter($user['data']['trophies'], function ($trophy) {
            return $trophy['pivot']['display'] === 1;
        });
        $achievements = $userModel->achievements;
        $filteredAchievements = $achievements->filter(function ($achievement) {
            return $achievement->display == 1 && $achievement->status == 1;
        });
        $user['data']['trophies'] = array_values($filteredTrophies);
        $user['data']['achievements'] = array_values($filteredAchievements->toArray());

        // Add social links
        $user['data']['social_links'] = [
            'twitter' => $userModel->social_twitter,
            'twitch' => $userModel->social_twitch,
            'youtube' => $userModel->social_youtube,
            'instagram' => $userModel->social_instagram,
            'discord' => $userModel->social_discord_tag,
            'website' => $userModel->social_website,
        ];

        // Add featured slots
        $user['data']['featured_slots'] = $userModel->featured_slots;

        $followStatus = Auth::user() ? FollowService::checkSubscriptionStatus(Auth::user(), $user['data']['id']) : null;

        return response()->json([
            'user' => $user,
            'followStatus' => $followStatus
        ], ResponseAlias::HTTP_OK);
    }
```

**Verify:** No artisan command needed.

---

## Step 8 — Connect Rewards.vue and sub-components to real API

**Edit file:** `resources/web/js/pages/Rewards.vue`

In the `methods`, update `handleBuyLevel`:

```js
    handleBuyLevel(level) {
      api.post('/api/rewards/buy-level', { level_id: level.id }).then(response => {
        if (response.status === 200) {
          store.state.notification = { message: 'Level purchased!', type: 'success', show: true };
          // Refresh balances
          this.$store.commit('updateHeaderData');
        }
      }).catch(() => {
        store.state.notification = { message: 'Could not purchase level', type: 'error', show: true };
      });
    },
    handleConvert(amount) {
      api.post('/api/rewards/convert', { ambar_amount: amount }).then(response => {
        if (response.status === 200) {
          this.showConvertModal = false;
          store.state.notification = {
            message: 'Converted ' + response.data.ambar_spent + ' Ambar to ' + response.data.uru_received + ' Uru',
            type: 'success', show: true
          };
          this.$store.commit('updateHeaderData');
        }
      }).catch(() => {
        store.state.notification = { message: 'Conversion failed', type: 'error', show: true };
      });
    },
```

Add the import at the top of the `<script>`:

```js
import api from "../api/api.js";
```

**Edit file:** `resources/web/js/pages/RewardsComponents/BattlePass.vue`

Replace the hardcoded `levels` in `data()` with an empty array, and add a `mounted()` hook:

```js
  data() {
    return {
      levels: [],
    };
  },
  mounted() {
    this.fetchLevels();
  },
  methods: {
    async fetchLevels() {
      try {
        const response = await api.get('/api/rewards/battle-pass');
        if (response.data && response.data.levels) {
          this.levels = response.data.levels.map(level => ({
            id: level.id,
            number: level.number,
            name: level.name,
            cost: level.cost_uru,
            status: this.computeStatus(level, response.data.levels),
            rewards: (level.rewards || []).map(r => ({
              label: r.type === 'ambar' ? '+' + r.amount + ' Ambar' : (r.label || r.type),
              type: r.type === 'ambar' ? 'ambar' : 'key',
            })),
          }));
        }
      } catch (error) {
        console.error('BattlePass fetch error:', error);
      }
    },
    computeStatus(level, allLevels) {
      if (level.owned) {
        // Check if it's the highest owned
        const ownedLevels = allLevels.filter(l => l.owned);
        const maxOwned = Math.max(...ownedLevels.map(l => l.number));
        if (level.number === maxOwned) return 'current';
        return 'owned';
      }
      // Check if previous level is owned
      const prev = allLevels.find(l => l.number === level.number - 1);
      if (!prev || prev.owned) return 'next';
      return 'locked';
    },
```

Add `import api from "../../api/api.js";` at the top of the script.

**Edit file:** `resources/web/js/pages/RewardsComponents/RewardsShop.vue`

Replace hardcoded data with API calls. Replace `data()` and add `mounted()`:

```js
  data() {
    return {
      shopItems: [],
      purchaseHistory: [],
    };
  },
  mounted() {
    this.fetchShop();
    this.fetchHistory();
  },
  methods: {
    async fetchShop() {
      try {
        const response = await api.get('/api/rewards/shop');
        if (response.data && response.data.items) {
          this.shopItems = response.data.items.map(item => ({
            id: item.id,
            name: item.name,
            description: item.description || '',
            price: item.price_uru,
            icon: item.name.substring(0, 2).toUpperCase(),
          }));
        }
      } catch (error) {
        console.error('Shop fetch error:', error);
      }
    },
    async fetchHistory() {
      try {
        const response = await api.get('/api/rewards/shop/history');
        if (response.data && response.data.purchases) {
          this.purchaseHistory = response.data.purchases;
        }
      } catch (error) {
        console.error('Purchase history fetch error:', error);
      }
    },
    buyItem(item) {
      if (this.userUru < item.price) return;
      api.post('/api/rewards/shop/buy', { item_id: item.id }).then(response => {
        if (response.status === 200) {
          store.state.notification = { message: item.name + ' purchased!', type: 'success', show: true };
          this.fetchHistory();
          this.$store.commit('updateHeaderData');
        }
      }).catch(() => {
        store.state.notification = { message: 'Purchase failed', type: 'error', show: true };
      });
    },
  },
```

Add `import api from "../../api/api.js";` at the top of the script.

**After all edits, run:** `npm run dev`

---

## Step 9 — VirtualHall.vue: Copy Link → Edit VHall + copy icon

**Edit file:** `resources/web/js/pages/VirtualHall/VirtualHall.vue`

Find this block in the template:

```html
      <button class="vh-btn vh-btn--secondary vh-profile__copy" @click="copyLink">
        Copy Link
      </button>
```

Replace with:

```html
      <button v-if="isOwnProfile" class="vh-btn vh-btn--secondary vh-profile__copy" @click="$router.push('/profile')">
        Edit VHall
      </button>
      <button v-else class="vh-btn vh-btn--secondary vh-profile__copy" @click="copyLink">
        Copy Link
      </button>
```

Find this block in the template:

```html
      <div class="vh-banner__url" @click="copyLink" title="Copy link">
        trophyroom.gg/{{ user.username }}
      </div>
```

Replace with:

```html
      <div class="vh-banner__url">
        <span>trophyroom.gg/{{ user.username }}</span>
        <span class="vh-copy-icon" @click="copyLink" title="Copy link">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
          </svg>
        </span>
      </div>
```

Add a computed property `isOwnProfile` in the script:

```js
    isOwnProfile() {
      if (!this.isLoggedIn || !this.user) return false;
      const currentUser = JSON.parse(localStorage.getItem('user'));
      return currentUser && currentUser.username === this.user.username;
    },
```

Add this CSS at the end of the `<style>` block:

```css
.vh-copy-icon {
  cursor: pointer;
  margin-left: 6px;
  opacity: 0.5;
  transition: opacity 0.2s;
  display: inline-flex;
  vertical-align: middle;
}
.vh-copy-icon:hover {
  opacity: 1;
}
.vh-banner__url {
  display: flex;
  align-items: center;
}
```

**After editing, run:** `npm run dev`

---

## Step 10 — Connect Profile.vue Virtual Hall section to real API

**Edit file:** `resources/web/js/pages/Profile.vue`

Add `import api from "../api/api.js";` if not already imported (it should be from the original code).

Replace the `saveVirtualHall` method:

```js
    saveVirtualHall() {
      api.put('/api/profile/update-virtual-hall', {
        social_twitter: this.socialLinks.twitter,
        social_twitch: this.socialLinks.twitch,
        social_youtube: this.socialLinks.youtube,
        social_instagram: this.socialLinks.instagram,
        social_discord_tag: this.socialLinks.discord,
        social_website: this.socialLinks.website,
        featured_slots: this.featuredSlots,
      }).then(response => {
        if (response.status === 201) {
          store.state.notification = { message: 'Virtual Hall updated', type: 'success', show: true };
        } else {
          store.state.notification = { message: 'Virtual Hall not updated', type: 'error', show: true };
        }
      }).catch(() => {
        store.state.notification = { message: 'Virtual Hall not updated', type: 'error', show: true };
      });
    },
```

In the `profileIndex` method, after the line `this.country_id = user.country_id;`, add:

```js
                // Load social links
                this.socialLinks.twitter = user.social_twitter || '';
                this.socialLinks.twitch = user.social_twitch || '';
                this.socialLinks.youtube = user.social_youtube || '';
                this.socialLinks.instagram = user.social_instagram || '';
                this.socialLinks.discord = user.social_discord_tag || '';
                this.socialLinks.website = user.social_website || '';

                // Load featured slots
                if (user.featured_slots) {
                  this.featuredSlots = user.featured_slots;
                }
```

NOTE: For this to work, the `ProfileResource` or `UserResource` must include the new fields. Check if it auto-includes all fillable fields. If not, you may need to add the social fields to the resource. Check with:

```bash
grep -n "social\|featured" app/Http/Resources/ProfileResource.php app/Http/Resources/UserResource.php
```

If neither resource explicitly maps fields (i.e., it uses `$this->resource` or `toArray` with all attributes), it should work automatically since we added the fields to `$fillable`. If the resource explicitly lists fields, add the social link fields to it.

**After editing, run:** `npm run dev`

---

## Step 11 — Verify and commit

1. `npm run dev` — zero errors
2. All new model files exist in `app/Models/`
3. `RewardsService.php` and `RewardsController.php` exist
4. `routes/api.php` has the rewards routes
5. VirtualHall.vue has Edit VHall button + copy icon
6. Profile.vue saveVirtualHall connects to real API

**Commit:**

```bash
git add -A && git commit -m "feat: backend for Rewards (battle pass, shop, convert) + Virtual Hall (social links, featured, edit button)"
```

**Do NOT push yet. Wait for Max to review.**

---

## Post-deploy steps (run on server after push)

After deploying to server, SSH in and run:

```bash
cd /var/www/ambar && php artisan migrate
```

This creates the new tables (battle_pass_levels, battle_pass_user, shop_items, shop_purchases) and adds social link columns to users.

---

## Files changed summary

| File | Action |
|------|--------|
| `database/migrations/xxxx_add_rewards_and_vhall_fields.php` | **NEW** — migration |
| `app/Models/BattlePassLevel.php` | **NEW** |
| `app/Models/ShopItem.php` | **NEW** |
| `app/Models/ShopPurchase.php` | **NEW** |
| `app/Models/User.php` | **MODIFIED** — fillable, casts, relationships |
| `app/Services/Api/RewardsService.php` | **NEW** |
| `app/Http/Controllers/Api/RewardsController.php` | **NEW** |
| `app/Services/Api/UserService.php` | **MODIFIED** — add updateVirtualHall |
| `app/Http/Controllers/Api/User/ProfileController.php` | **MODIFIED** — add updateVirtualHall |
| `app/Http/Controllers/Api/User/VirtualHallController.php` | **MODIFIED** — return social links + featured |
| `routes/api.php` | **MODIFIED** — add rewards + virtual-hall routes |
| `resources/web/js/pages/Rewards.vue` | **MODIFIED** — connect to real API |
| `resources/web/js/pages/RewardsComponents/BattlePass.vue` | **MODIFIED** — fetch from API |
| `resources/web/js/pages/RewardsComponents/RewardsShop.vue` | **MODIFIED** — fetch from API |
| `resources/web/js/pages/VirtualHall/VirtualHall.vue` | **MODIFIED** — Edit VHall + copy icon |
| `resources/web/js/pages/Profile.vue` | **MODIFIED** — connect saveVirtualHall to API |
