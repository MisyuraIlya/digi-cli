<?php

namespace App\Controller;

use App\helpers\ApiResponse;
use PHPUnit\Util\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SupportController extends AbstractController
{

    #[Route('/support/send', name: 'report', methods: ['POST'])]
    public function report(Request $request, MailerInterface $mailer): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->json((new ApiResponse(null, 'Invalid JSON payload'))->OnError());
            }

            // Extract values from JSON body
            $name = $data['name'] ?? null;
            $phone = $data['phone'] ?? null;
            $email = $data['email'] ?? null;
            $userExtId = $data['userExtId'] ?? null;
            $message = $data['message'] ?? null;
            $bussnies = $data['bussnies'] ?? null;

            $email = (new Email())
                ->from('statosbiz@statos.co')
                ->to('bake@margaret.co.il')
                ->subject( "הודעה ממערכת B2B")
                ->text('')
                ->html('
                <div style="text-align: right; direction: rtl;">
                    <h3 style="font-family: arial,sans-serif;"><strong>שם מלא: </strong><span>'.$name.'</span></h3>
                    <table style="font-family: arial,sans-serif; border-collapse: collapse; width: 100%;">
                        <tr>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">כתובת מייל</td>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">'.$email.'</td>
                        </tr>
                         <tr>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">מספר טלפון</td>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">'.$phone.'</td>
                        </tr>
                         <tr>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">מספר לקוח</td>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">'.$userExtId.'</td>
                        </tr>
                         <tr>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">שם עסק</td>
                            <td style="border: 1px solid #dddddd;text-align: right;padding: 8px;">'.$bussnies.'</td>
                        </tr>
                    </table>
                    <h3 style="font-family: arial,sans-serif;">הודעה</h3>
                    <pre style="font-family: arial,sans-serif;white-space: pre-wrap;">'.$message.'</pre>
                </div>
                ');
            $mailer->send($email);
            return $this->json((new ApiResponse('',''))->OnSuccess());
        } catch (\Exception $e) {
            return $this->json((new ApiResponse(null,'שגיאה ' . $e->getMessage()))->OnError());
        }
    }

}