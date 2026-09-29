<?php
/**
 * Starter blog posts. Copied into storage/blog/ the first time the blog is
 * used; after that, manage all posts in Admin → Blog posts (edit or delete these freely).
 */

return [
    [
        'slug'     => 'multi-factor-authentication-explained',
        'title'    => 'Multi-Factor Authentication Explained: The Simplest Way to Protect Your Accounts',
        'category' => 'cybersecurity',
        'tags'     => ['MFA', 'Account security', 'Passwords'],
        'featured' => true,
        'excerpt'  => 'A stolen password is far less dangerous when your account asks for a second proof of identity. Here is what multi-factor authentication is, which type to choose and how to set it up without locking yourself out.',
        'body'     => <<<'MD'
Passwords get stolen every day through data breaches, phishing emails and reused logins. **Multi-factor authentication (MFA)** adds a second check, so a stolen password alone is not enough to get into your account.

## What is multi-factor authentication?

MFA asks for two or more kinds of proof when you sign in:

- Something you **know**, like a password
- Something you **have**, like your phone or a security key
- Something you **are**, like a fingerprint or face scan

When MFA is on, someone who steals your password still needs your phone or key to get in.

## Which type of MFA should I use?

Not all methods are equally strong. From strongest to weakest:

1. **Security keys and passkeys.** A physical key or a passkey stored on your device. Very resistant to phishing.
2. **Authenticator apps.** Apps such as Microsoft Authenticator or Google Authenticator generate a code that changes every 30 seconds.
3. **Text message (SMS) codes.** Better than nothing, but codes can be intercepted or redirected through SIM-swap scams.

Any MFA is far better than none. If a service only offers text messages, turn it on anyway.

## Where to turn it on first

Start with the accounts that could unlock everything else:

- Your main **email** account (it can reset your other passwords)
- **Banking** and payment apps
- Your **Microsoft 365** or **Google Workspace** account
- Your **website host**, **domain registrar** and social media accounts

## Don't lock yourself out

When you set up MFA, most services give you **backup or recovery codes**. Save them somewhere safe and offline, such as a password manager or a printed copy in a secure place. Also check that your recovery phone number and email address are current.

> A few minutes of setup today can prevent weeks of trouble recovering a hijacked account.

## Need a hand?

We help individuals, families and small businesses set up MFA properly on the accounts that matter most, with backup options so nobody gets locked out. [Book a consultation](/book-a-consultation) and we will walk you through it.
MD,
    ],
    [
        'slug'     => 'how-to-spot-a-phishing-email',
        'title'    => '7 Signs an Email Is a Phishing Attempt',
        'category' => 'cybersecurity',
        'tags'     => ['Phishing', 'Email security', 'Scams'],
        'featured' => false,
        'excerpt'  => 'Phishing emails are designed to rush you into clicking. Learn the seven warning signs to look for, and what to do if you already clicked.',
        'body'     => <<<'MD'
Phishing is one of the most common ways accounts and businesses are compromised. The good news: most phishing messages share a handful of warning signs you can learn to spot.

## 1. It creates urgency or fear

"Your account will be closed in 24 hours." "Unusual sign-in detected." Pressure is designed to make you act before you think.

## 2. The sender address doesn't match

The display name might say your bank, but the actual address is something unexpected. Check the full email address, not just the name.

## 3. Links go somewhere unexpected

Hover over a link (or press and hold on a phone) to preview where it really goes before you tap it. Look closely for misspelled domain names.

## 4. It asks for passwords, codes or payment

Legitimate companies don't ask you to confirm your password, share a verification code or buy gift cards by email.

## 5. There's an unexpected attachment

Invoices, shipping notices and "documents" you weren't expecting are a common way to deliver malware, even when they appear to come from someone you know.

## 6. The greeting or tone feels off

Generic greetings, unusual wording or requests that feel out of character for the sender are all red flags.

## 7. It's too good to be true

Prizes, refunds and "exclusive" opportunities are classic lures.

## What to do if you clicked

1. Don't panic, and don't enter any more information.
2. From a device you trust, change the password for the affected account, plus any account that used the same password.
3. Turn on multi-factor authentication if it isn't already on.
4. If money or banking details are involved, call your bank using a number you already trust.
5. Tell your IT contact so they can check for anything suspicious.

## Train your team

Phishing awareness is a skill that improves with practice. Our [cybersecurity awareness training](/training) helps teams, families and community groups recognize and report phishing with confidence.
MD,
    ],
    [
        'slug'     => 'small-business-website-essentials',
        'title'    => 'What Every Small Business Website Needs',
        'category' => 'web-development',
        'tags'     => ['Small business', 'Website design', 'SEO'],
        'featured' => false,
        'excerpt'  => 'Your website is often a customer’s first impression. These essentials help it earn trust, rank in search and turn visitors into inquiries.',
        'body'     => <<<'MD'
A good small business website doesn't need to be complicated. It needs to answer a visitor's questions quickly and make it easy to take the next step.

## A clear first impression

Within a few seconds, visitors should know **what you do**, **who you help** and **how to contact you**. Put this at the top of your homepage in plain language.

## Easy ways to get in touch

Include a clear call to action on every page, such as "Book a consultation" or "Request a quote". Keep contact forms short and ask only for what you need.

## Fast and mobile-friendly

Most visitors will arrive on a phone. Your site should load quickly, use readable text sizes and have buttons that are easy to tap.

## Trust signals that are real

Real client testimonials, clear service descriptions, an about page with real people and transparent contact details all build credibility. Avoid vague claims you can't back up.

## Security basics

- Use **HTTPS** on every page
- Keep your website software, themes and plugins **updated**
- Use strong, unique passwords and **MFA** for admin accounts
- Take regular **backups** and confirm you can restore them

## Search engine basics

Give every page a unique title and description, use clear headings, describe images with alt text and write content that answers the questions your customers actually ask.

## Ready for a website that works?

We design and build fast, secure, mobile-friendly websites for small businesses, startups and professionals. [Explore our web development services](/web-development).
MD,
    ],
    [
        'slug'     => 'the-3-2-1-backup-rule',
        'title'    => 'The 3-2-1 Backup Rule: A Simple Plan to Protect Your Files',
        'category' => 'cloud',
        'tags'     => ['Backups', 'Ransomware', 'Data protection'],
        'featured' => false,
        'excerpt'  => 'Hardware fails, laptops get lost and ransomware happens. The 3-2-1 rule is an easy way to make sure your important files survive.',
        'body'     => <<<'MD'
Losing important files is stressful, whether it is family photos, client records or years of business documents. A simple, proven approach is the **3-2-1 backup rule**.

## The rule

- Keep **3** copies of your data: the original plus two backups.
- Store them on **2** different types of storage, such as a cloud service and an external drive.
- Keep **1** copy offsite or in the cloud, away from your main location.

## Why it works

Different problems destroy different copies. A failed hard drive, a stolen laptop, a fire or a ransomware attack rarely affects all three copies at once.

## Make it automatic

Backups that depend on someone remembering usually stop happening. Use tools that back up automatically, such as built-in options in Microsoft 365, Google Workspace or your computer's operating system.

## Test your restores

A backup you have never restored is a hope, not a plan. Every few months, try restoring a file or folder to confirm everything works.

## Protect your backups too

Ransomware can target connected backups. Keep at least one copy that is disconnected or protected with versioning, and secure backup accounts with strong passwords and MFA.

## Need help setting it up?

Our [cloud and digital technology services](/cloud-services) include backup planning and setup for individuals and growing businesses.
MD,
    ],
];
