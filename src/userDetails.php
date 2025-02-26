<?php

/**
 * UserDetails.php
 *
 * This file extracts **non-personally identifiable** data about users who access the web app.
 * It gathers **general location**, **ISP details**, **device type**, **operating system**, and **browser info**.
 *
 * ### **Why This Exists:**
 * - 🏆 **User Metrics** → Tracks how many people have used the app (cool to see!)
 * - 🌎 **Geographical Reach** → Seeing users from all over the world is awesome!
 * - 📱 **Device Type Breakdown** → Helps decide if updates should prioritize mobile vs. desktop.
 * - 🖥 **Browser & OS Info** → Useful for diagnosing errors and fixing edge case bugs.
 * - 🔧 **Moderation Tool** → If someone uploads inappropriate content, their IP can be added to a **restricted access list**.
 *
 * ### **Privacy & Ethics:**
 * - ❌ **No personal data is stored**. This **cannot** track or identify specific individuals.
 * - 🌍 **Location data is approximate** (city-level at best).
 */

function getUserDetails(string $userIP, string $userAgent): array {
    $location = $region = $zip = $timezone = $lat = $lon = $isp = $asn = "Unknown";
    $mobile = $proxy = $hosting = false;
    $browser = $browserVersion = $os = $osVersion = $deviceType = "Unknown";

    try {
        $geoResponse = @file_get_contents("http://ip-api.com/json/$userIP?fields=status,country,regionName,city,zip,lat,lon,timezone,isp,as,mobile,proxy,hosting");
        if ($geoResponse) {
            $geoData = json_decode($geoResponse, true);
            if (($geoData['status'] ?? '') === 'success') {
                $location = trim(($geoData['city'] ?? '') . ", " . ($geoData['country'] ?? ''), ', ') ?: "Unknown";
                $region = $geoData['regionName'] ?? "Unknown";
                $zip = $geoData['zip'] ?? "Unknown";
                $timezone = $geoData['timezone'] ?? "Unknown";
                $lat = $geoData['lat'] ?? "Unknown";
                $lon = $geoData['lon'] ?? "Unknown";
                $isp = $geoData['isp'] ?? "Unknown";
                $asn = $geoData['as'] ?? "Unknown";
                $mobile = $geoData['mobile'] ?? false;
                $proxy = $geoData['proxy'] ?? false;
                $hosting = $geoData['hosting'] ?? false;
            }
        }
    } catch (Exception $e) {
        error_log("[ERROR] Failed to retrieve location data: " . $e->getMessage());
    }

    try {
        $parsedUA = parseUserAgent($userAgent);
        $browser = $parsedUA['browser'] ?? "Unknown";
        $browserVersion = $parsedUA['browser_version'] ?? "Unknown";
        $os = $parsedUA['os'] ?? "Unknown";
        $osVersion = $parsedUA['os_version'] ?? "Unknown";
        $deviceType = $parsedUA['device_type'] ?? "Unknown";
    } catch (Exception $e) {
        error_log("[ERROR] Failed to parse user agent: " . $e->getMessage());
    }

    return [
        "location" => $location,
        "region" => $region,
        "zip" => $zip,
        "timezone" => $timezone,
        "latitude" => $lat,
        "longitude" => $lon,
        "isp" => $isp,
        "asn" => $asn,
        "mobile" => $mobile,
        "proxy" => $proxy,
        "hosting" => $hosting,
        "browser" => $browser,
        "browser_version" => $browserVersion,
        "os" => $os,
        "os_version" => $osVersion,
        "device_type" => $deviceType
    ];
}

/**
 * Custom User-Agent Parser
 * Extracts Browser, OS, and Device Type from the user agent string.
 */
function parseUserAgent(string $userAgent): array {
    $browser = "Unknown";
    $browserVersion = "Unknown";
    $os = "Unknown";
    $osVersion = "Unknown";
    $deviceType = "Unknown";

    // Detect Browser
    if (preg_match('/Edge\/([0-9._]+)/', $userAgent, $matches)) {
        $browser = "Microsoft Edge";
        $browserVersion = $matches[1];
    } elseif (preg_match('/Chrome\/([0-9._]+)/', $userAgent, $matches)) {
        $browser = "Google Chrome";
        $browserVersion = $matches[1];
    } elseif (preg_match('/Firefox\/([0-9._]+)/', $userAgent, $matches)) {
        $browser = "Mozilla Firefox";
        $browserVersion = $matches[1];
    } elseif (preg_match('/Safari\/([0-9._]+)/', $userAgent, $matches) && !preg_match('/Chrome/', $userAgent)) {
        $browser = "Apple Safari";
        $browserVersion = $matches[1];
    } elseif (preg_match('/MSIE ([0-9._]+)/', $userAgent, $matches) || preg_match('/Trident\/.*rv:([0-9._]+)/', $userAgent, $matches)) {
        $browser = "Internet Explorer";
        $browserVersion = $matches[1];
    }

    // Detect OS
    if (preg_match('/Windows NT ([0-9._]+)/', $userAgent, $matches)) {
        $os = "Windows";
        $osVersion = $matches[1];
    } elseif (preg_match('/Mac OS X ([0-9._]+)/', $userAgent, $matches)) {
        $os = "Mac OS";
        $osVersion = str_replace("_", ".", $matches[1]);
    } elseif (preg_match('/Linux/', $userAgent)) {
        $os = "Linux";
    } elseif (preg_match('/Android ([0-9._]+)/', $userAgent, $matches)) {
        $os = "Android";
        $osVersion = $matches[1];
    } elseif (preg_match('/iPhone OS ([0-9._]+)/', $userAgent, $matches)) {
        $os = "iOS";
        $osVersion = str_replace("_", ".", $matches[1]);
    } elseif (preg_match('/iPad; CPU OS ([0-9._]+)/', $userAgent, $matches)) {
        $os = "iOS";
        $osVersion = str_replace("_", ".", $matches[1]);
    }

    // Detect Device Type
    if (preg_match('/Mobile|Android|iPhone/', $userAgent)) {
        $deviceType = "Mobile";
    } elseif (preg_match('/iPad|Tablet/', $userAgent)) {
        $deviceType = "Tablet";
    } else {
        $deviceType = "Desktop";
    }

    return [
        "browser" => $browser,
        "browser_version" => $browserVersion,
        "os" => $os,
        "os_version" => $osVersion,
        "device_type" => $deviceType
    ];
}
