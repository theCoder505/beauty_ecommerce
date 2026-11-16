<?php

use App\Models\Admin;
use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createTestAdmin(): Admin
{
    return Admin::create([
        'name' => 'Admin User',
        'email' => 'admin@example.com',
        'password' => Hash::make('password123'),
    ]);
}

test('unauthenticated users cannot delete newsletter subscribers', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'test@example.com',
        'is_active' => true,
    ]);

    $response = $this->delete(route('admin.subscribers.destroy', $subscriber));
    $response->assertRedirect(route('admin.login'));
    $this->assertDatabaseHas('newsletter_subscribers', ['id' => $subscriber->id]);
});

test('unauthenticated users cannot bulk delete newsletter subscribers', function () {
    $subscriber = NewsletterSubscriber::create([
        'email' => 'test@example.com',
        'is_active' => true,
    ]);

    $response = $this->post(route('admin.subscribers.bulk-delete'), [
        'ids' => [$subscriber->id],
    ]);
    $response->assertRedirect(route('admin.login'));
    $this->assertDatabaseHas('newsletter_subscribers', ['id' => $subscriber->id]);
});

test('admin can view newsletter audience with delete buttons and bulk delete action', function () {
    $admin = createTestAdmin();
    $subscriber = NewsletterSubscriber::create([
        'email' => 'subscriber@example.com',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'admin')->get(route('admin.subscribers.index'));

    $response->assertOk();
    $response->assertSee('Newsletter Audience');
    $response->assertSee('subscriber@example.com');
    $response->assertSee(route('admin.subscribers.bulk-delete'));
    $response->assertSee(route('admin.subscribers.destroy', $subscriber));
});

test('admin can delete a single newsletter subscriber', function () {
    $admin = createTestAdmin();
    $subscriber = NewsletterSubscriber::create([
        'email' => 'delete_me@example.com',
        'is_active' => true,
    ]);

    $response = $this->actingAs($admin, 'admin')->delete(route('admin.subscribers.destroy', $subscriber));

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Subscriber removed successfully.');
    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $subscriber->id]);
});

test('admin can bulk delete multiple newsletter subscribers', function () {
    $admin = createTestAdmin();
    $sub1 = NewsletterSubscriber::create(['email' => 'user1@example.com', 'is_active' => true]);
    $sub2 = NewsletterSubscriber::create(['email' => 'user2@example.com', 'is_active' => true]);
    $sub3 = NewsletterSubscriber::create(['email' => 'keep@example.com', 'is_active' => true]);

    $response = $this->actingAs($admin, 'admin')->post(route('admin.subscribers.bulk-delete'), [
        'ids' => [$sub1->id, $sub2->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', '2 subscribers deleted successfully.');

    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $sub1->id]);
    $this->assertDatabaseMissing('newsletter_subscribers', ['id' => $sub2->id]);
    $this->assertDatabaseHas('newsletter_subscribers', ['id' => $sub3->id]);
});

test('bulk delete requires valid subscriber ids', function () {
    $admin = createTestAdmin();

    $response = $this->actingAs($admin, 'admin')->post(route('admin.subscribers.bulk-delete'), [
        'ids' => [],
    ]);
    $response->assertSessionHasErrors(['ids']);

    $responseInvalid = $this->actingAs($admin, 'admin')->post(route('admin.subscribers.bulk-delete'), [
        'ids' => [999999],
    ]);
    $responseInvalid->assertSessionHasErrors(['ids.0']);
});
