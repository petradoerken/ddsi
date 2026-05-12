// api/send-email.js
// This is a Vercel serverless function - create this file in your GitHub repo at: api/send-email.js

export default async function handler(req, res) {
  // Enable CORS
  res.setHeader('Access-Control-Allow-Origin', '*');
  res.setHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type');

  // Handle OPTIONS request for CORS
  if (req.method === 'OPTIONS') {
    return res.status(200).end();
  }

  // Only allow POST requests
  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Method not allowed' });
  }

  const { first_name, last_name, organization, title, email, inquiry_type, message } = req.body;

  // Validate required fields
  if (!first_name || !last_name || !organization || !title || !email || !inquiry_type || !message) {
    return res.status(400).json({ success: false, message: 'Missing required fields' });
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    return res.status(400).json({ success: false, message: 'Invalid email address' });
  }

  // Email content
  const emailBody = `
New inquiry from DDSI website

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

CONTACT INFORMATION

Name: ${first_name} ${last_name}
Email: ${email}
Organization: ${organization}
Title: ${title}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

INQUIRY TYPE

${inquiry_type}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

MESSAGE

${message}

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Submitted: ${new Date().toLocaleString('en-US', { dateStyle: 'long', timeStyle: 'short' })}
IP Address: ${req.headers['x-forwarded-for'] || req.connection.remoteAddress}
  `.trim();

  // Using Resend API (free tier: 100 emails/day, 3000/month)
  // You'll need to add RESEND_API_KEY to your Vercel environment variables
  try {
    const response = await fetch('https://api.resend.com/emails', {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${process.env.RESEND_API_KEY}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        from: 'DDSI Website <onboarding@resend.dev>',
        to: ['petra@ddlegacycapital.com'],
        reply_to: email,
        subject: `DDSI Contact Form: ${inquiry_type} - ${first_name} ${last_name}`,
        text: emailBody
      })
    });

    if (!response.ok) {
      throw new Error('Email service error');
    }

    return res.status(200).json({ success: true, message: 'Email sent successfully' });
  } catch (error) {
    console.error('Email error:', error);
    return res.status(500).json({ success: false, message: 'Failed to send email' });
  }
}
