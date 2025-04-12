<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class TelegramAuthService {
    const STATE_WAITING_EMAIL = 'waiting_email';
    const STATE_WAITING_PASSWORD = 'waiting_password';
    const CACHE_TTL = 3600; // 1 час

    public function startAuth(int $chatId): void {
        $this->setState($chatId, self::STATE_WAITING_EMAIL);
    }

    public function getState(int $chatId): ?string {
        return Cache::get("telegram_auth_state_{$chatId}");
    }

    public function setState(int $chatId, string $state, ?array $data = null): void {
        Cache::put("telegram_auth_state_{$chatId}", $state, self::CACHE_TTL);
        if ($data) {
            Cache::put("telegram_auth_data_{$chatId}", $data, self::CACHE_TTL);
        }
    }

    public function getStateData(int $chatId): ?array {
        return Cache::get("telegram_auth_data_{$chatId}");
    }

    public function clearState(int $chatId): void {
        Cache::forget("telegram_auth_state_{$chatId}");
        Cache::forget("telegram_auth_data_{$chatId}");
    }

    public function handleEmail(int $chatId, string $email): array {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return [
                'success' => false,
                'message' => '❌ Пользователь с таким email не найден. Пожалуйста, проверьте правильность ввода.',
            ];
        }

        $this->setState($chatId, self::STATE_WAITING_PASSWORD, ['email' => $email]);

        return [
            'success' => true,
            'message' => '📝 Теперь введите ваш пароль:',
        ];
    }

    public function handlePassword(int $chatId, string $password): array {
        $data = $this->getStateData($chatId);
        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return [
                'success' => false,
                'message' => '❌ Неверный пароль. Пожалуйста, попробуйте еще раз.',
            ];
        }

        // Привязываем Telegram ID к пользователю
        $user->update(['telegram_id' => (string) $chatId]);
        $this->clearState($chatId);

        return [
            'success' => true,
            'message' =>
                "✅ Авторизация успешна! Добро пожаловать, {$user->first_name}!\n\n" .
                'Теперь вы можете отправлять мне ссылки на Pull Request-ы, ' .
                'и я буду уведомлять ревьюверов вашей команды.',
        ];
    }

    public function isAuthenticated(int $chatId): bool {
        return User::where('telegram_id', (string) $chatId)->exists();
    }
}
