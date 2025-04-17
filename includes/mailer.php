<?php
// Include Composer autoload
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configure SMTP
        $this->mailer->isSMTP();
        $this->mailer->Host = SMTP_HOST;
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = SMTP_EMAIL;
        $this->mailer->Password = SMTP_PASSWORD;
        $this->mailer->SMTPSecure = SMTP_SECURE;
        $this->mailer->Port = SMTP_PORT;
    }
    
    public function sendOTP($email, $otp) {
        try {
            $this->mailer->setFrom(SMTP_EMAIL, APP_NAME);
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Verify Your Email - Notes App';
            
            // Email-client friendly HTML template with inline styles
            $emailTemplate = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f8fafc;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc;">
                    <tr>
                        <td align="center" style="padding: 40px 0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                <!-- Header -->
                                <tr>
                                    <td align="center" style="padding: 40px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center">
                                                    <div style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 32px; font-weight: bold; color: #2563eb;">
                                                        📝 Notes App
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Welcome Message -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #4b5563; padding: 20px 0;">
                                                    Hello,<br><br>
                                                    Welcome to Notes App! Please use the verification code below to complete your email verification:
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- OTP Container -->
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%" style="background: linear-gradient(135deg, #dbeafe, #eff6ff); border-radius: 12px;">
                                            <tr>
                                                <td align="center" style="padding: 30px;">
                                                    <div style="
                                                        font-family: \'Segoe UI\', Arial, sans-serif;
                                                        font-size: 42px;
                                                        font-weight: 800;
                                                        letter-spacing: 12px;
                                                        color: #1e40af;
                                                        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                                                        background-color: #ffffff;
                                                        padding: 20px 40px;
                                                        border-radius: 8px;
                                                        border: 1px solid rgba(59, 130, 246, 0.1);
                                                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                                                        ' . $otp . '
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Message -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #64748b; padding: 20px 0;">
                                                    This verification code will expire in 10 minutes for security purposes.<br>
                                                    If you didn\'t request this code, please ignore this email.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td align="center" style="padding: 30px 0; border-top: 1px solid #e2e8f0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center" style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; color: #64748b;">
                                                    © ' . date('Y') . ' Notes App. All rights reserved.<br>
                                                    Keep your notes organized and secure.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="padding-top: 20px;">
                                                    <div style="
                                                        font-family: \'Segoe UI\', Arial, sans-serif;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #2563eb;">
                                                        Developed with ❤️ by 
                                                        <a href="https://techycsr.me" target="_blank" style="color: #2563eb; text-decoration: none;">@TechyCSR</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
            
            $this->mailer->Body = $emailTemplate;
            $this->mailer->AltBody = "Your OTP is: {$otp}";
            
            return $this->mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function sendPasswordResetCode($email, $resetCode) {
        try {
            $this->mailer->setFrom(SMTP_EMAIL, APP_NAME);
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'Password Reset Code - ' . APP_NAME;
            
            // Email-client friendly HTML template with inline styles
            $emailTemplate = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f8fafc;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc;">
                    <tr>
                        <td align="center" style="padding: 40px 0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                <!-- Header -->
                                <tr>
                                    <td align="center" style="padding: 40px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center">
                                                    <div style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 32px; font-weight: bold; color: #2563eb;">
                                                        📝 ' . APP_NAME . '
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Message -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #4b5563; padding: 20px 0;">
                                                    Hello,<br><br>
                                                    You requested a password reset. Please use the verification code below to reset your password:
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Reset Code Container -->
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%" style="background: linear-gradient(135deg, #fee2e2, #fef2f2); border-radius: 12px;">
                                            <tr>
                                                <td align="center" style="padding: 30px;">
                                                    <div style="
                                                        font-family: \'Segoe UI\', Arial, sans-serif;
                                                        font-size: 42px;
                                                        font-weight: 800;
                                                        letter-spacing: 12px;
                                                        color: #991b1b;
                                                        text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
                                                        background-color: #ffffff;
                                                        padding: 20px 40px;
                                                        border-radius: 8px;
                                                        border: 1px solid rgba(220, 38, 38, 0.1);
                                                        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);">
                                                        ' . $resetCode . '
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Additional Info -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #64748b; padding: 20px 0;">
                                                    This password reset code will expire in 30 minutes for security purposes.<br>
                                                    If you didn\'t request this password reset, please ignore this email or contact support if you have concerns.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td align="center" style="padding: 30px 0; border-top: 1px solid #e2e8f0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center" style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; color: #64748b;">
                                                    © ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.<br>
                                                    Keep your notes organized and secure.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="padding-top: 20px;">
                                                    <div style="
                                                        font-family: \'Segoe UI\', Arial, sans-serif;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #2563eb;">
                                                        Developed with ❤️ by 
                                                        <a href="https://techycsr.me" target="_blank" style="color: #2563eb; text-decoration: none;">@TechyCSR</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
            
            $this->mailer->Body = $emailTemplate;
            $this->mailer->AltBody = "Your password reset code is: {$resetCode}";
            
            return $this->mailer->send();
        } catch (Exception $e) {
            error_log("Mailer error: " . $e->getMessage());
            return false;
        }
    }
    
    public function sendLoginNotification($email, $name, $ip_address, $login_time) {
        try {
            $this->mailer->setFrom(SMTP_EMAIL, APP_NAME);
            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = 'New Login Detected - ' . APP_NAME;
            
            // Format the date and time to be more readable with IST indicator
            $formatted_time = date('F j, Y g:i A', strtotime($login_time)) . ' IST';
            
            // Email-client friendly HTML template with inline styles
            $emailTemplate = '
            <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            </head>
            <body style="margin: 0; padding: 0; background-color: #f8fafc;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f8fafc;">
                    <tr>
                        <td align="center" style="padding: 40px 0;">
                            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background: #ffffff; border-radius: 16px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                <!-- Header -->
                                <tr>
                                    <td align="center" style="padding: 40px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center">
                                                    <div style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 32px; font-weight: bold; color: #2563eb;">
                                                        📝 ' . APP_NAME . '
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Message -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; line-height: 1.6; color: #4b5563; padding: 20px 0;">
                                                    Hello ' . htmlspecialchars($name) . ',<br><br>
                                                    We detected a new login to your ' . APP_NAME . ' account. Here are the details:
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Login Details Container -->
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%" style="background: linear-gradient(135deg, #e0f2fe, #e0f7fa); border-radius: 12px;">
                                            <tr>
                                                <td style="padding: 30px;">
                                                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                        <tr>
                                                            <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; font-weight: 600; color: #0284c7; padding-bottom: 10px;">
                                                                IP Address:
                                                            </td>
                                                            <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; color: #334155; text-align: right;">
                                                                ' . htmlspecialchars($ip_address) . '
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; font-weight: 600; color: #0284c7; padding-bottom: 10px; padding-top: 10px;">
                                                                Date & Time (IST):
                                                            </td>
                                                            <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 16px; color: #334155; text-align: right;">
                                                                ' . htmlspecialchars($formatted_time) . '
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Security Message -->
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #64748b; padding: 20px 0;">
                                                    If this was you, no action is needed. This email is simply to keep you informed about account activity.<br><br>
                                                    If you did not login at this time, please change your password immediately and contact support.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Footer -->
                                <tr>
                                    <td align="center" style="padding: 30px 0; border-top: 1px solid #e2e8f0;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="80%">
                                            <tr>
                                                <td align="center" style="font-family: \'Segoe UI\', Arial, sans-serif; font-size: 14px; color: #64748b;">
                                                    © ' . date('Y') . ' ' . APP_NAME . '. All rights reserved.<br>
                                                    Keep your notes organized and secure.
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="padding-top: 20px;">
                                                    <div style="
                                                        font-family: \'Segoe UI\', Arial, sans-serif;
                                                        font-size: 14px;
                                                        font-weight: 600;
                                                        color: #2563eb;">
                                                        Developed with ❤️ by 
                                                        <a href="https://techycsr.me" target="_blank" style="color: #2563eb; text-decoration: none;">@TechyCSR</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';
            
            $this->mailer->Body = $emailTemplate;
            $this->mailer->AltBody = "Hello {$name}, A new login was detected for your account.\nIP Address: {$ip_address}\nTime: {$formatted_time}\n\nIf this wasn't you, please change your password immediately.";
            
            return $this->mailer->send();
        } catch (Exception $e) {
            return false;
        }
    }
} 