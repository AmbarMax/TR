<?php

namespace App\Models;

use App\Http\Traits\Models\User\GettersTrait;
use App\Http\Traits\UUID;
use App\Repositories\CentrifugoRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;

/**
 * @property string $id
 * @property string $name
 * @property string $username
 * @property string $email
 * @property string $phone_number
 * @property Carbon $date_of_birth
 * @property Carbon $email_verified_at
 * @property string $avatar
 * @property string $background
 * @property string $password
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, GettersTrait, UUID, HasRoles, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'date_of_birth',
        'avatar',
        'background',
        'email_verified_at',
        'password',
        'country_id',
        'description',
        'google2fa_secret',
        'google2fa_status',
        'secret_key',
        'social_twitter',
        'social_twitch',
        'social_youtube',
        'social_instagram',
        'social_discord_tag',
        'social_website',
        'featured_slots',
        'source',
        'account_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'featured_slots' => 'array',
        'is_featured' => 'boolean',
        'verified_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'format_date_of_birth',
        'format_email_verified_at'
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function generateCentrifugeToken()
    {
        $centrifugo = new CentrifugoRepository();
        return $centrifugo->generateToken($this);
    }

    public function getUserDataWithBalances()
    {
        return $this->load('balances.currency', 'roles');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class)->withPivot(['display','is_share']);
    }

    public function trophies()
    {
        return $this->belongsToMany(Trophy::class)->withPivot(['display', 'token_id']);
    }

    public function keys()
    {
        return $this->belongsToMany(Key::class)->withPivot(['display', 'token_id']);
    }

    public function unAvailableTrophies()
    {
        return $this->belongsToMany(Trophy::class, 'unavailable_trophy_user', 'user_id', 'trophy_id');
    }

    public function publicBadges()
    {
        return $this->belongsToMany(Badge::class)->where('display', true);
    }

    public function sharedBadges()
    {
        return $this->belongsToMany(Badge::class)->where('is_share', true);
    }

    public function authIntegration()
    {
        return $this->hasOne(AuthIntegration::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function balancesWithCurrencies(): HasMany
    {
        return $this->hasMany(Balance::class)
            ->join('currencies', 'balances.currency_id', '=', 'currencies.id')
            ->select('balances.*', 'currencies.name as currency_name');
    }

    public function getBalanceByCurrencyName($currencyName)
    {
        return $this->balancesWithCurrencies()->whereHas('currency', function ($query) use ($currencyName) {
            $query->where('name', $currencyName);
        })->first();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id')->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id')->withTimestamps();
    }

    public function followersCount(): int
    {
        return $this->followers()->count();
    }

    public function followingCount(): int
    {
        return $this->following()->count();
    }

    public function isFollowing($userId): bool
    {
        return $this->following()->where('follower_id', $userId)->exists();
    }

    public function isFollower($userId, $followerId): bool
    {
        return Follower::where('user_id', $userId)->where('follower_id', $followerId)->exists();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id', 'id');
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('read', false)->count();
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class, 'user_id', 'id');
    }

    public function setGoogle2faSecretAttribute ($value)
    {
        $this->attributes['google2fa_secret'] = encrypt($value);
    }

    public function getGoogle2faSecretAttribute ($value)
    {
        if ($value !== null) {
            return decrypt($value);
        } else {
            return $value;
        }
    }
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['account_type', 'username'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('user');
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($user) {
            $user->roles()->detach();
        });

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Str::uuid()->toString();
        });
    }

    public function chests()
    {
        return $this->belongsToMany(Chest::class, 'chest_user', 'user_id', 'chest_id')
            ->withPivot('is_open')->withPivot('id');
    }

    public function steamGames()
    {
        return $this->belongsToMany(SteamGame::class, 'steam_user_games')
            ->withPivot('playtime_minutes')
            ->withTimestamps();
    }

    public function steamAchievements()
    {
        return $this->belongsToMany(SteamAchievement::class, 'steam_user_achievements')
            ->withPivot('unlocked_at')
            ->withTimestamps();
    }

    public function battlePassLevels()
    {
        return $this->belongsToMany(BattlePassLevel::class, 'battle_pass_user')
            ->withTimestamps();
    }

    public function shopPurchases()
    {
        return $this->hasMany(ShopPurchase::class);
    }

    public function getPlayerStats(): array
    {
        return [
            'badges'           => $this->badges()->count(),
            'trophies'         => $this->trophies()->count(),
            'achievements'     => $this->achievements()->count(),
            'platforms_count'  => \DB::table('auth_integrations')
                ->where('user_id', $this->id)
                ->whereNull('deleted_at')
                ->count(),
        ];
    }

    public function getBrandStats(): array
    {
        return [
            // TODO Step 10: count of users currently online/active in the brand's
            //      guild. Requires a live presence source (Centrifugo or DB flag).
            'active_now'    => 0,

            'issued_total'  => \DB::table('trophies')
                ->where('user_id', $this->id)
                ->whereNull('deleted_at')
                ->count(),

            // TODO Step 10: count of distinct users who own at least one trophy
            //      issued by this brand. Join trophy_user + trophies on user_id.
            'conquerors'    => 0,

            'followers'     => $this->followers()->count(),
        ];
    }
}
