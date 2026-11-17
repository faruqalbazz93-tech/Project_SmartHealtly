<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Core\Validator;

class InputValidationTest extends TestCase
{
    /**
     * Test required field validation fails when empty
     */
    public function testRequiredValidationFailsWhenEmpty(): void
    {
        $validator = Validator::make([
            'name' => '',
        ], [
            'name' => 'required',
        ]);

        $this->assertTrue($validator->fails(), 'Validator harus gagal jika field wajib kosong.');
        $this->assertNotEmpty($validator->errors()['name']);
    }

    /**
     * Test valid email passes and invalid email fails
     */
    public function testEmailValidation(): void
    {
        $validValidator = Validator::make([
            'email' => 'pasien.testing@smarthealthy.id',
        ], [
            'email' => 'required|email',
        ]);
        $this->assertFalse($validValidator->fails(), 'Email yang valid harus lolos validasi.');

        $invalidValidator = Validator::make([
            'email' => 'bukan-email-valid',
        ], [
            'email' => 'required|email',
        ]);
        $this->assertTrue($invalidValidator->fails(), 'Email tidak valid harus gagal validasi.');
    }

    /**
     * Test Indonesian WhatsApp / phone number validation
     */
    public function testWhatsAppPhoneValidation(): void
    {
        $validValidator = Validator::make([
            'phone' => '081234567890',
        ], [
            'phone' => 'required|whatsapp',
        ]);
        $this->assertFalse($validValidator->fails(), 'Nomor WhatsApp berformat 08xxx harus lolos validasi.');

        $invalidValidator = Validator::make([
            'phone' => '12345',
        ], [
            'phone' => 'required|whatsapp',
        ]);
        $this->assertTrue($invalidValidator->fails(), 'Nomor telepon yang terlalu pendek harus gagal validasi.');
    }

    /**
     * Test password confirmation match rule
     */
    public function testPasswordConfirmationMatches(): void
    {
        $matching = Validator::make([
            'password'         => 'SandiKuat@2026',
            'password_confirm' => 'SandiKuat@2026',
        ], [
            'password'         => 'required|min:6|confirmed',
            'password_confirm' => 'required',
        ]);
        $this->assertFalse($matching->fails(), 'Password yang sama harus lolos validasi.');

        $mismatch = Validator::make([
            'password'         => 'SandiKuat@2026',
            'password_confirm' => 'SandiBeda#9999',
        ], [
            'password'         => 'required|min:6|confirmed',
            'password_confirm' => 'required',
        ]);
        $this->assertTrue($mismatch->fails(), 'Password yang tidak cocok harus gagal validasi.');
    }

    /**
     * Test NIK 16 digit numeric validation
     */
    public function testNikValidation(): void
    {
        $validNik = Validator::make([
            'nik' => '3209141205950002',
        ], [
            'nik' => 'required|numeric|min:16|max:16',
        ]);
        $this->assertFalse($validNik->fails(), 'NIK 16 digit harus lolos validasi.');

        $invalidNik = Validator::make([
            'nik' => '3209141205',
        ], [
            'nik' => 'required|numeric|min:16|max:16',
        ]);
        $this->assertTrue($invalidNik->fails(), 'NIK kurang dari 16 digit harus gagal validasi.');
    }
}
