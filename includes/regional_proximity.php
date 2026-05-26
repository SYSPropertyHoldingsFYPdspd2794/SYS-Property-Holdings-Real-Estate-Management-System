<?php

function regional_proximity_amenities(string $state): array
{
    $default = [
        ['name' => 'Regional Shopping Centre', 'type' => 'Shopping Mall', 'distance' => '3.4 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
        ['name' => 'General Hospital', 'type' => 'Healthcare', 'distance' => '5.8 KM', 'icon' => 'fas fa-hospital text-success'],
        ['name' => 'Public School Cluster', 'type' => 'Education', 'distance' => '2.6 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
        ['name' => 'Main Transport Terminal', 'type' => 'Transit', 'distance' => '6.2 KM', 'icon' => 'fas fa-subway text-warning'],
    ];

    $amenities = [
        'Johor' => [
            ['name' => 'Johor Bahru City Square', 'type' => 'Shopping Mall', 'distance' => '4.2 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Sultanah Aminah', 'type' => 'Healthcare', 'distance' => '6.7 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Teknologi Malaysia', 'type' => 'Education', 'distance' => '8.4 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'JB Sentral', 'type' => 'Transit', 'distance' => '5.1 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Kedah' => [
            ['name' => 'Aman Central', 'type' => 'Shopping Mall', 'distance' => '4.8 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Sultanah Bahiyah', 'type' => 'Healthcare', 'distance' => '6.3 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Utara Malaysia', 'type' => 'Education', 'distance' => '9.5 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Alor Setar Bus Terminal', 'type' => 'Transit', 'distance' => '5.6 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Kelantan' => [
            ['name' => 'KB Mall', 'type' => 'Shopping Mall', 'distance' => '4.1 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Raja Perempuan Zainab II', 'type' => 'Healthcare', 'distance' => '6.9 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Kelantan', 'type' => 'Education', 'distance' => '8.7 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Terminal Kota Bharu', 'type' => 'Transit', 'distance' => '5.2 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Melaka' => [
            ['name' => 'Dataran Pahlawan Melaka Megamall', 'type' => 'Shopping Mall', 'distance' => '3.9 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Melaka', 'type' => 'Healthcare', 'distance' => '5.4 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Multimedia University Melaka', 'type' => 'Education', 'distance' => '7.8 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Melaka Sentral', 'type' => 'Transit', 'distance' => '4.6 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Negeri Sembilan' => [
            ['name' => 'Palm Mall Seremban', 'type' => 'Shopping Mall', 'distance' => '4.3 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Tuanku Jaafar', 'type' => 'Healthcare', 'distance' => '5.9 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Sains Islam Malaysia', 'type' => 'Education', 'distance' => '9.2 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Terminal One Seremban', 'type' => 'Transit', 'distance' => '4.7 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Pahang' => [
            ['name' => 'East Coast Mall', 'type' => 'Shopping Mall', 'distance' => '5.2 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Tengku Ampuan Afzan', 'type' => 'Healthcare', 'distance' => '6.4 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Pahang', 'type' => 'Education', 'distance' => '8.9 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Terminal Kuantan Sentral', 'type' => 'Transit', 'distance' => '5.7 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Perak' => [
            ['name' => 'Ipoh Parade', 'type' => 'Shopping Mall', 'distance' => '4.6 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Raja Permaisuri Bainun', 'type' => 'Healthcare', 'distance' => '5.8 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Teknologi Petronas', 'type' => 'Education', 'distance' => '9.8 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Ipoh Railway Station', 'type' => 'Transit', 'distance' => '5.1 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Perlis' => [
            ['name' => 'Kangar Jaya Mall', 'type' => 'Shopping Mall', 'distance' => '3.6 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Tuanku Fauziah', 'type' => 'Healthcare', 'distance' => '5.5 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Perlis', 'type' => 'Education', 'distance' => '7.3 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Kangar Bus Terminal', 'type' => 'Transit', 'distance' => '4.1 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Penang' => [
            ['name' => 'Gurney Plaza', 'type' => 'Shopping Mall', 'distance' => '4.4 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Penang General Hospital', 'type' => 'Healthcare', 'distance' => '5.6 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Sains Malaysia', 'type' => 'Education', 'distance' => '7.5 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Butterworth Railway Station', 'type' => 'Transit', 'distance' => '6.8 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Pulau Pinang' => [
            ['name' => 'Gurney Plaza', 'type' => 'Shopping Mall', 'distance' => '4.4 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Penang General Hospital', 'type' => 'Healthcare', 'distance' => '5.6 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Sains Malaysia', 'type' => 'Education', 'distance' => '7.5 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Butterworth Railway Station', 'type' => 'Transit', 'distance' => '6.8 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Sabah' => [
            ['name' => 'Imago Shopping Mall', 'type' => 'Shopping Mall', 'distance' => '4.9 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Queen Elizabeth Hospital', 'type' => 'Healthcare', 'distance' => '6.1 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Sabah', 'type' => 'Education', 'distance' => '8.6 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Kota Kinabalu Terminal', 'type' => 'Transit', 'distance' => '5.3 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Sarawak' => [
            ['name' => 'The Spring Kuching', 'type' => 'Shopping Mall', 'distance' => '4.7 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Sarawak General Hospital', 'type' => 'Healthcare', 'distance' => '6.2 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Sarawak', 'type' => 'Education', 'distance' => '9.1 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Kuching Sentral', 'type' => 'Transit', 'distance' => '5.9 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Selangor' => [
            ['name' => 'Setia City Mall', 'type' => 'Shopping Mall', 'distance' => '4.2 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Shah Alam', 'type' => 'Healthcare', 'distance' => '5.3 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Teknologi MARA Shah Alam', 'type' => 'Education', 'distance' => '6.9 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Shah Alam KTM Station', 'type' => 'Transit', 'distance' => '4.8 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Terengganu' => [
            ['name' => 'KTCC Mall', 'type' => 'Shopping Mall', 'distance' => '4.5 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Sultanah Nur Zahirah', 'type' => 'Healthcare', 'distance' => '6.5 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Sultan Zainal Abidin', 'type' => 'Education', 'distance' => '8.3 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Terminal Bas Kuala Terengganu', 'type' => 'Transit', 'distance' => '5.4 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Kuala Lumpur' => [
            ['name' => 'Pavilion Kuala Lumpur', 'type' => 'Shopping Mall', 'distance' => '4.0 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Kuala Lumpur', 'type' => 'Healthcare', 'distance' => '5.2 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaya', 'type' => 'Education', 'distance' => '7.9 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'KL Sentral', 'type' => 'Transit', 'distance' => '5.0 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Labuan' => [
            ['name' => 'Financial Park Labuan', 'type' => 'Shopping Mall', 'distance' => '3.1 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Labuan', 'type' => 'Healthcare', 'distance' => '4.8 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Universiti Malaysia Sabah Labuan', 'type' => 'Education', 'distance' => '6.4 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Labuan Ferry Terminal', 'type' => 'Transit', 'distance' => '3.8 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
        'Putrajaya' => [
            ['name' => 'IOI City Mall', 'type' => 'Shopping Mall', 'distance' => '5.0 KM', 'icon' => 'fas fa-shopping-bag text-primary'],
            ['name' => 'Hospital Putrajaya', 'type' => 'Healthcare', 'distance' => '4.4 KM', 'icon' => 'fas fa-hospital text-success'],
            ['name' => 'Heriot-Watt University Malaysia', 'type' => 'Education', 'distance' => '6.7 KM', 'icon' => 'fas fa-graduation-cap text-danger'],
            ['name' => 'Putrajaya Sentral', 'type' => 'Transit', 'distance' => '4.2 KM', 'icon' => 'fas fa-subway text-warning'],
        ],
    ];

    return $amenities[$state] ?? $default;
}

function regional_proximity_map_query(array $property): string
{
    return trim(($property['project_name'] ?? '') . ', ' . ($property['state'] ?? '') . ', Malaysia');
}
