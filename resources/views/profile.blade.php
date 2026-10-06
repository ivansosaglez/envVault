<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Profile" subtitle="Manage your account details and password." />
    </x-slot>

    <div class="max-w-2xl space-y-6">
        <x-card><livewire:profile.update-profile-information-form /></x-card>
        <x-card><livewire:profile.update-password-form /></x-card>
        <x-card><livewire:profile.delete-user-form /></x-card>
    </div>
</x-app-layout>
