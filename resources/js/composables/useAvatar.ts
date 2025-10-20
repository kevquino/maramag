import type { User } from '@/types';

export function hasImageAvatar(user: User): boolean {
    if (!user.avatar) return false;
    
    return user.avatar !== '' && user.avatar.startsWith('/images/avatars/');
}

export function getAvatarUrl(user: User): string {
    if (!hasImageAvatar(user)) return '';
    
    // If it's already a full URL, use it as-is
    if (user.avatar!.startsWith('http')) {
        return user.avatar!;
    }
    
    // Otherwise, it's a relative path from your public folder
    return user.avatar!;
}

export function useAvatar() {
    return {
        hasImageAvatar,
        getAvatarUrl
    };
}