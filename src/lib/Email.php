<?php

namespace kuro\lib;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件服务
 */
class Email
{
    /**
     * SMTP 服务
     *
     * @var string
     */
    private $host;

    /**
     * 服务端口
     *
     * @var int
     */
    private $port;

    /**
     * 服务器用户名
     *
     * @var string
     */
    private $username;

    /**
     * 服务器密码
     *
     * @var string
     */
    private $password;

    /**
     * 服务发件邮箱
     *
     * @var string
     */
    private $fromEmail;

    /**
     * 发布邮件姓名
     *
     * @var string
     */
    private $company;

    public function __construct(string $platform = 'aliyun')
    {
        $config = config('email.'.$platform);
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->username = $config['username'];
        $this->password = $config['password'];
        $this->fromEmail = $config['fromEmail'];
        $this->company = $config['company'];
    }


    /**
     * SMTP发送邮件
     *
     * @param string $toMail 收件人邮箱
     * @param string $name 收件人姓名
     * @param string $subject 标题
     * @param string $body 邮件内容
     * @param array|null $attachment 附件
     * @return bool
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function sendMail(string $toMail, string $name, string $subject = '', string $body = '', array $attachment = null): bool
    {
        $mail = new PHPMailer();                                // 实例化PHPMailer对象
        $mail->CharSet = 'UTF-8';                               // 设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                                        // 设定使用SMTP服务
        $mail->SMTPDebug = 0;                                   // SMTP调试功能 0=关闭 1 = 错误和消息 2 = 消息
        $mail->SMTPAuth = true;                                 // 启用 SMTP 验证功能
        $mail->SMTPSecure = 'ssl';                              // 使用安全协议
        $mail->Host = $this->host;                              // SMTP 服务器
        $mail->Port = $this->port;                              // SMTP服务器的端口号
        $mail->Username = $this->username;                      // SMTP服务器用户名
        $mail->Password = $this->password;                      // SMTP服务器密码
        $mail->SetFrom($this->fromEmail, $this->company);

        $replyEmail = '';                                       //留空则为发件人EMAIL
        $replyName = '';                                        //回复名称（留空则为发件人名称）
        $mail->AddReplyTo($replyEmail, $replyName);
        $mail->Subject = $subject;
        $mail->MsgHTML($body);
        $mail->AddAddress($toMail, $name);
        if (is_array($attachment)) {                            // 添加附件
            foreach ($attachment as $file) {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
        return $mail->Send();
    }

    /**
     * 获取发送平台
     *
     * @return string
     */
    public function getCompany(): string
    {
        return $this->company;
    }
}