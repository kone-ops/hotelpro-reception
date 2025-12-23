<?php

if (!function_exists('getDeviceName')) {
    /**
     * Extraire le nom de l'appareil depuis le User-Agent
     */
    function getDeviceName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Appareil inconnu';
        }
        
        if (preg_match('/Mobile|Android|iPhone|iPad/i', $userAgent)) {
            if (preg_match('/iPhone/i', $userAgent)) {
                return 'iPhone';
            }
            if (preg_match('/iPad/i', $userAgent)) {
                return 'iPad';
            }
            if (preg_match('/Android/i', $userAgent)) {
                return 'Android';
            }
            return 'Mobile';
        }
        
        if (preg_match('/Windows/i', $userAgent)) {
            return 'Windows';
        }
        if (preg_match('/Mac/i', $userAgent)) {
            return 'Mac';
        }
        if (preg_match('/Linux/i', $userAgent)) {
            return 'Linux';
        }
        
        return 'Ordinateur';
    }
}

if (!function_exists('getBrowserName')) {
    /**
     * Extraire le nom du navigateur depuis le User-Agent
     */
    function getBrowserName(?string $userAgent): string
    {
        if (!$userAgent) {
            return 'Navigateur inconnu';
        }
        
        if (preg_match('/Chrome/i', $userAgent) && !preg_match('/Edg|OPR/i', $userAgent)) {
            return 'Chrome';
        }
        if (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox';
        }
        if (preg_match('/Safari/i', $userAgent) && !preg_match('/Chrome/i', $userAgent)) {
            return 'Safari';
        }
        if (preg_match('/Edg/i', $userAgent)) {
            return 'Edge';
        }
        if (preg_match('/OPR/i', $userAgent)) {
            return 'Opera';
        }
        
        return 'Navigateur inconnu';
    }
}

