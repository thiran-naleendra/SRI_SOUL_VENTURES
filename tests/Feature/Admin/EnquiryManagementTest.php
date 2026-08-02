<?php

namespace Tests\Feature\Admin;

use App\Models\ContactEnquiry;
use App\Models\CustomTourRequest;
use App\Models\Destination;
use App\Models\Package;
use App\Models\PackageEnquiry;
use App\Models\TravelStyle;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_package_enquiry_search_filters_workflow_whatsapp_and_pagination(): void
    {
        $package = Package::factory()->create(['title' => 'Island Explorer']);
        PackageEnquiry::factory()->count(20)->create();
        $enquiry = PackageEnquiry::factory()->for($package)->create(['customer_name' => 'Needle Customer', 'email' => 'needle@example.com', 'phone' => '0771234567', 'whatsapp_number' => '+94 77 123 4567', 'status' => 'new']);
        $this->actingAs($this->superAdmin());

        $this->get(route('admin.package-enquiries.index'))->assertOk()->assertSee('page=2', false);
        $this->get(route('admin.package-enquiries.index', ['search' => 'Needle', 'package' => $package->id, 'status' => 'new']))->assertOk()->assertSee('Needle Customer')->assertDontSee('page=2', false);
        $this->get(route('admin.package-enquiries.show', $enquiry))->assertOk()->assertSee('https://wa.me/94771234567', false)->assertSee('Island Explorer');
        $this->put(route('admin.package-enquiries.update', $enquiry), ['status' => 'quotation_sent', 'admin_notes' => 'Prepared quote', 'mark_contacted' => 1])->assertRedirect()->assertSessionHas('success');
        $enquiry->refresh();
        $this->assertSame('quotation_sent', $enquiry->status);
        $this->assertSame('Prepared quote', $enquiry->admin_notes);
        $this->assertNotNull($enquiry->contacted_at);
    }

    public function test_package_enquiry_csv_uses_filters_and_escapes_formula_cells(): void
    {
        $package = Package::factory()->create();
        PackageEnquiry::factory()->for($package)->create(['customer_name' => '=DANGEROUS', 'status' => 'confirmed']);
        PackageEnquiry::factory()->for($package)->create(['customer_name' => 'Excluded', 'status' => 'new']);
        $this->actingAs($this->superAdmin());
        $response = $this->get(route('admin.package-enquiries.export', ['status' => 'confirmed']));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $csv = $response->streamedContent();
        $this->assertStringContainsString("'=DANGEROUS", $csv);
        $this->assertStringNotContainsString('Excluded', $csv);
    }

    public function test_custom_tour_details_assignment_timestamps_filters_and_csv(): void
    {
        $consultant = User::factory()->create();
        $consultant->assignRole('tour_consultant');
        $destination = Destination::factory()->create();
        $style = TravelStyle::factory()->create();
        $tour = CustomTourRequest::factory()->create(['customer_name' => 'Custom Needle', 'whatsapp_number' => '+94770000000', 'status' => 'new']);
        $tour->destinations()->attach($destination);
        $tour->travelStyles()->attach($style);
        $this->actingAs($this->superAdmin());

        $this->get(route('admin.custom-tour-requests.show', $tour))->assertOk()->assertSee($destination->name)->assertSee($style->name)->assertSee('https://wa.me/94770000000', false);
        $this->put(route('admin.custom-tour-requests.update', $tour), ['assigned_user_id' => $consultant->id, 'status' => 'confirmed', 'admin_notes' => 'Booked', 'mark_contacted' => 1, 'mark_quotation_sent' => 1, 'mark_confirmed' => 1])->assertRedirect();
        $tour->refresh();
        $this->assertTrue($tour->assignedUser->is($consultant));
        $this->assertNotNull($tour->contacted_at);
        $this->assertNotNull($tour->quotation_sent_at);
        $this->assertNotNull($tour->confirmed_at);
        $this->get(route('admin.custom-tour-requests.index', ['search' => 'Needle', 'status' => 'confirmed', 'consultant' => $consultant->id]))->assertOk()->assertSee('Custom Needle');
        $csv = $this->get(route('admin.custom-tour-requests.export', ['consultant' => $consultant->id]))->streamedContent();
        $this->assertStringContainsString($destination->name, $csv);
        $this->assertStringContainsString($style->name, $csv);
        $this->assertStringContainsString($consultant->name, $csv);
    }

    public function test_contact_enquiry_search_read_workflow_and_spam_only_deletion(): void
    {
        $enquiry = ContactEnquiry::create(['name' => 'Needle Sender', 'email' => 'sender@example.com', 'subject' => 'A question', 'message' => 'Full message', 'status' => 'new', 'is_read' => false]);
        ContactEnquiry::create(['name' => 'Other', 'email' => 'other@example.com', 'message' => 'Other', 'status' => 'resolved', 'is_read' => true]);
        $this->actingAs($this->superAdmin());
        $this->get(route('admin.contact-enquiries.index', ['search' => 'Needle', 'status' => 'new', 'read' => 'unread']))->assertOk()->assertSee('Needle Sender')->assertDontSee('other@example.com');
        $this->get(route('admin.contact-enquiries.show', $enquiry))->assertOk()->assertSee('Full message');
        $this->assertTrue($enquiry->fresh()->is_read);
        $this->put(route('admin.contact-enquiries.update', $enquiry), ['status' => 'contacted', 'admin_notes' => 'Replied', 'is_read' => 0])->assertRedirect();
        $enquiry->refresh();
        $this->assertFalse($enquiry->is_read);
        $this->assertSame('Replied', $enquiry->admin_notes);
        $this->delete(route('admin.contact-enquiries.destroy', $enquiry))->assertStatus(422);
        $this->assertDatabaseHas('contact_enquiries', ['id' => $enquiry->id]);
        $enquiry->update(['status' => 'spam']);
        $this->delete(route('admin.contact-enquiries.destroy', $enquiry))->assertRedirect(route('admin.contact-enquiries.index'));
        $this->assertDatabaseMissing('contact_enquiries', ['id' => $enquiry->id]);
    }

    public function test_permissions_and_status_validation_are_enforced(): void
    {
        $packageEnquiry = PackageEnquiry::factory()->create();
        $tour = CustomTourRequest::factory()->create();
        $this->get(route('admin.package-enquiries.index'))->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get(route('admin.custom-tour-requests.show', $tour))->assertForbidden();
        $this->actingAs($this->superAdmin())->put(route('admin.package-enquiries.update', $packageEnquiry), ['status' => 'invalid'])->assertSessionHasErrors('status');
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        return $user;
    }
}
