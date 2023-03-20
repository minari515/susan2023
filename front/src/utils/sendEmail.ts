/**
 *
 * 未使用
 * VercelからGmailを飛ばすことが出来なかったため(原因不明)
 *
 */

import nodemailer from "nodemailer";
import Mail from "nodemailer/lib/mailer";
import SMTPTransport from "nodemailer/lib/smtp-transport";

const transportOptions = {
	host: "smtp.gmail.com",
	port: 465,
	secure: true, // SSL
	auth: {
		user: process.env.SENDER_GMAIL_ADDRESS!,
		pass: process.env.SENDER_GMAIL_PASSWORD!,
	},
};

const mailOptions = (
	subject: string,
	message: string,
	questionIndex: number
): Mail.Options => ({
	from: process.env.SENDER_GMAIL_ADDRESS,
	to: process.env.RECEIVER_MAIL_ADDRESS,
	subject: `【SUSAN】${subject}`,
	text: `${message}
  
  確認するにはこちらのURLをクリックしてください．
  https://liff.line.me/${process.env.NEXT_PUBLIC_LIFF_ID}/question/${questionIndex}`,
});

const sendEmail = async (
	subject: string,
	message: string,
	questionIndex: number
) => {
	const transporter = nodemailer.createTransport(transportOptions);
	const mail = mailOptions(subject, message, questionIndex);
	let result = {} as SMTPTransport.SentMessageInfo;
	try {
		await transporter.sendMail(mail, (error, info) => {
			if (error) {
				throw error;
			}
			result = info;
		});
		return result;
	} catch (error: any) {
		throw new Error(`sendEmail: ${error.message || "unknown error"}`);
	}
};

export default sendEmail;
