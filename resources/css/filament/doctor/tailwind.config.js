import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Doctor/**/*.php',
        './resources/**/*.blade.php',
        './resources/views/filament/doctor/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
