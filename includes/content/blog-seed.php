<?php
/**
 * Starter blog posts. Copied into storage/blog/ the first time the blog is
 * used; after that, manage all posts in Admin → Blog posts (edit or delete these freely).
 */

return [
    [
        'slug'     => 'multi-factor-authentication-explained',
        'cover'    => 'post-mfa',
        'title'    => 'Multi-factor authentication explained, and how to turn it on',
        'category' => 'cybersecurity',
        'tags'     => ['MFA', 'Account security', 'Passwords'],
        'featured' => true,
        'excerpt'  => 'A stolen password is far less dangerous when your account asks for a second proof of identity. What MFA is, which type to choose and how to set it up without locking yourself out.',
        'body'     => <<<'MD'
Passwords get stolen every day through data breaches, phishing emails and reused logins. **Multi-factor authentication (MFA)** adds a second check, so a stolen password alone is not enough to get into your account.

## What is multi-factor authentication?

MFA asks for two or more kinds of proof when you sign in:

- something you know, like a password
- something you have, like your phone or a security key
- something you are, like a fingerprint or face scan

When MFA is on, someone who steals your password still needs your phone or key to get in.

## Which type of MFA should I use?

Not all methods are equally strong. From strongest to weakest:

1. Security keys and passkeys: a physical key, or a passkey stored on your device. These are very hard to phish.
2. Authenticator apps, such as Microsoft Authenticator or Google Authenticator, which show a code that changes every 30 seconds.
3. Text message (SMS) codes. These are better than nothing, but criminals can intercept or redirect them with SIM-swap scams.

Any MFA is far better than none. If a service only offers text messages, turn it on anyway.

## Where to turn it on first

Start with the accounts that give access to everything else:

- your main email account, because it can reset your other passwords
- banking and payment apps
- your Microsoft 365 or Google Workspace account
- your website host, domain registrar and social media accounts

## Don't lock yourself out

When you set up MFA, most services give you backup or recovery codes. Save them somewhere safe, such as a password manager or a printed copy kept somewhere secure. Also check that your recovery phone number and email address are up to date.

## Need a hand?

I help individuals, families and small businesses set up MFA on their most important accounts, with backup options so nobody gets locked out. [Book a consultation](/book-a-consultation) and I will walk you through it.
MD,
    ],
    [
        'slug'     => 'how-to-spot-a-phishing-email',
        'cover'    => 'post-phishing',
        'title'    => '7 signs an email is a phishing attempt',
        'category' => 'cybersecurity',
        'tags'     => ['Phishing', 'Email security', 'Scams'],
        'featured' => false,
        'excerpt'  => 'Phishing emails try to rush you into clicking. Here are seven warning signs, and what to do if you already clicked.',
        'body'     => <<<'MD'
Phishing is one of the most common ways criminals get into accounts and businesses. Most phishing messages share a few warning signs, and you can learn to spot them.

## 1. It creates urgency or fear

"Your account will be closed in 24 hours." "Unusual sign-in detected." The pressure is there to make you act before you think.

## 2. The sender address doesn't match

The display name might say your bank, but the actual address is something unexpected. Check the full email address, not just the name.

## 3. Links go somewhere unexpected

Before you tap a link, hover over it (or press and hold on a phone) to see where it really goes. Look closely for misspelled domain names.

## 4. It asks for passwords, codes or payment

Legitimate companies don't ask you to confirm your password, share a verification code or buy gift cards by email.

## 5. There's an unexpected attachment

Invoices, shipping notices and "documents" you weren't expecting are a common way to deliver malware, even when they appear to come from someone you know.

## 6. The greeting or tone feels off

Be wary of generic greetings, odd wording, or a request that seems out of character for the sender.

## 7. It's too good to be true

Prizes, refunds and "exclusive" offers are common bait.

## What to do if you clicked

1. Don't panic, and don't enter any more information.
2. From a device you trust, change the password for the affected account, plus any account that used the same password.
3. Turn on multi-factor authentication if it isn't already on.
4. If money or banking details are involved, call your bank using a number you already trust.
5. Tell your IT contact so they can check for anything suspicious.

## Train your team

People get better at spotting phishing with practice. My [security awareness training](/training) teaches teams, families and community groups to recognize and report it.
MD,
    ],
    [
        'slug'     => 'small-business-website-essentials',
        'cover'    => 'post-website',
        'title'    => 'What every small business website needs',
        'category' => 'web-development',
        'tags'     => ['Small business', 'Website design', 'SEO'],
        'featured' => false,
        'excerpt'  => 'Your website is often the first thing a customer sees of your business. These basics help it earn trust, show up in search and bring in enquiries.',
        'body'     => <<<'MD'
A good small business website doesn't need to be complicated. It needs to answer a visitor's questions quickly and make it easy to take the next step.

## A clear first impression

Within a few seconds, visitors should know what you do, who you help and how to contact you. Put this at the top of your homepage in plain language.

## Easy ways to get in touch

Put a clear button on every page, such as "Book a consultation" or "Request a quote". Keep contact forms short and ask only for what you need.

## Fast and mobile-friendly

Most visitors will arrive on a phone. Your site should load quickly, use readable text sizes and have buttons that are easy to tap.

## Honest signs of trust

Real client testimonials, clear service descriptions, an about page with real people and visible contact details make visitors more willing to get in touch. Leave out claims you can't back up.

## Security basics

- Use HTTPS on every page.
- Keep your website software, themes and plugins updated.
- Use strong, unique passwords and MFA for admin accounts.
- Take regular backups and check that you can restore them.

## Search engine basics

Give every page a unique title and description, use clear headings, describe images with alt text and write content that answers the questions your customers actually ask.

## Need a website?

I design and build fast, secure websites that work on phones, for small businesses, startups and professionals. [See my web development service](/web-development).
MD,
    ],
    [
        'slug'     => 'the-3-2-1-backup-rule',
        'cover'    => 'post-backup',
        'title'    => 'The 3-2-1 backup rule: a simple plan to protect your files',
        'category' => 'cloud',
        'tags'     => ['Backups', 'Ransomware', 'Data protection'],
        'featured' => false,
        'excerpt'  => 'Hard drives fail, laptops get lost and ransomware happens. The 3-2-1 rule is an easy way to keep your important files safe.',
        'body'     => <<<'MD'
Losing important files is stressful, whether they are family photos, client records or years of business documents. The 3-2-1 backup rule is a simple, widely used way to avoid it.

## The rule

- Keep 3 copies of your data: the original plus two backups.
- Store them on 2 different types of storage, such as a cloud service and an external drive.
- Keep 1 copy offsite or in the cloud, away from your main location.

## Why it works

Different problems destroy different copies. A failed hard drive, a stolen laptop, a fire or a ransomware attack rarely affects all three copies at once.

## Make it automatic

Backups that depend on someone remembering usually stop happening. Use tools that back up automatically, such as built-in options in Microsoft 365, Google Workspace or your computer's operating system.

## Test your restores

Until you have restored from a backup, you do not know that it works. Every few months, try restoring a file or folder.

## Protect your backups too

Ransomware can target connected backups. Keep at least one copy that is disconnected or protected with versioning, and secure backup accounts with strong passwords and MFA.

## Need help setting it up?

My [cloud and digital technology service](/cloud-services) includes planning and setting up backups for individuals and small businesses.
MD,
    ],
];
