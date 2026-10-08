<?php
/**
 * Second set of starter blog posts (SEO articles). Added once to existing sites
 * as well as new ones; after that, manage them in Admin → Blog posts. A post you
 * delete is not added again.
 */

return [
    [
        'slug'     => 'how-to-secure-your-business-email',
        'cover'    => 'cyber',
        'title'    => 'How to Secure Your Business Email: A Practical Checklist',
        'meta_title' => 'How to Secure Your Business Email (Checklist) | Enoma',
        'meta_description' => 'A plain-language checklist to protect your business email in Microsoft 365 or Google Workspace: MFA, admin accounts, forwarding rules, SPF, DKIM and DMARC.',
        'category' => 'cybersecurity',
        'tags'     => ['Email security', 'Microsoft 365', 'Google Workspace'],
        'featured' => false,
        'excerpt'  => 'Your email account can reset every other password you have. Here is a practical checklist to lock it down, whether you use Microsoft 365, Google Workspace or another provider.',
        'body'     => <<<'MD'
For most businesses, email is the master key. If someone gets into your mailbox, they can reset passwords, read invoices, and send convincing messages to your clients in your name. The good news: a handful of settings closes most of the doors attackers use.

## 1. Turn on multi-factor authentication for everyone

Multi-factor authentication (MFA) means a stolen password is not enough to sign in. Turn it on for **every** mailbox, not only yours. An authenticator app (such as Microsoft Authenticator or Google Authenticator) is stronger than text-message codes.

Read more: [Multi-factor authentication explained](/blog/multi-factor-authentication-explained).

## 2. Keep admin accounts separate

The account that manages your Microsoft 365 or Google Workspace should not be the one you use for everyday email. Create a separate admin account, protect it with MFA, and only sign in to it when you need to change settings.

## 3. Check for forwarding rules you didn't create

A common trick after a break-in is a hidden rule that quietly forwards copies of your email to an outside address. Look in your mailbox settings for **forwarding** and **rules**, and remove anything you don't recognise.

## 4. Set up SPF, DKIM and DMARC

These three records live in your domain settings and help other mail servers recognise genuine email from your business:

- **SPF** lists the services allowed to send email for your domain
- **DKIM** adds a digital signature to your messages
- **DMARC** tells receiving servers what to do with email that fails those checks

Together they make it much harder for someone to send fake email that appears to come from you, and they help your real email avoid spam folders.

## 5. Teach your team to spot phishing

Most email break-ins start with a convincing message asking someone to sign in or open a file. A short, friendly training session goes a long way. See [7 signs an email is a phishing attempt](/blog/how-to-spot-a-phishing-email).

## 6. Have a plan for lost or stolen phones

If a staff member loses a phone with email on it, you should be able to sign it out remotely and reset their password. Find out where that option is in your admin console before you need it.

## Quick checklist

- MFA on for every mailbox
- Separate admin account
- No unknown forwarding rules
- SPF, DKIM and DMARC in place
- Team knows how to spot phishing
- Plan for lost devices

## Want it checked for you?

We review business email setups, fix the gaps and explain every change in plain language. [Book a consultation](/book-a-consultation) or see our [cybersecurity services](/cybersecurity).
MD,
    ],
    [
        'slug'     => 'signs-your-computer-has-been-hacked',
        'cover'    => 'itsupport',
        'title'    => 'Signs Your Computer May Have Been Hacked (and What to Do Next)',
        'meta_title' => 'Signs Your Computer Has Been Hacked: What to Do | Enoma',
        'meta_description' => 'Unexpected pop-ups, new programs, password changes you didn’t make? Learn the warning signs of a hacked computer and the calm, practical steps to take next.',
        'category' => 'it-support',
        'tags'     => ['Malware', 'Security', 'Troubleshooting'],
        'featured' => false,
        'excerpt'  => 'Strange pop-ups, programs you didn’t install or friends receiving odd messages from you? Here are the common warning signs and the steps to take, in the right order.',
        'body'     => <<<'MD'
Not every slow computer has been hacked, but some signs are worth taking seriously. Here is what to look for, and what to do, calmly and in the right order.

## Common warning signs

- **Programs or browser extensions you didn't install** suddenly appear
- **Your browser home page or search engine changed** on its own
- **Pop-ups appear** even when your browser is closed
- **Friends or clients receive messages from you** that you didn't send
- **You're told your password is wrong** for accounts you haven't changed
- **Your security software was switched off** and won't turn back on
- **The computer is suddenly much slower**, or the fan runs constantly while idle

One of these on its own can have an innocent explanation. Several together deserve attention.

## What to do next

### 1. Disconnect from the internet

Turn off Wi-Fi or unplug the network cable. This stops anything on the computer from sending more data out while you deal with it.

### 2. Change important passwords from a different device

Use a phone or another computer you trust to change the passwords for your **email**, **banking** and any account that showed signs of trouble. Start with email, because it can reset everything else.

### 3. Turn on multi-factor authentication

If it isn't on already, switch on [multi-factor authentication](/blog/multi-factor-authentication-explained) for those accounts so a stolen password alone can't be used.

### 4. Run a full security scan

Reconnect only when you're ready to update your security software, then run a full scan. Built-in tools such as Microsoft Defender are a good start.

### 5. Check your accounts for changes

Look for new forwarding rules in your email, unknown devices signed in to your accounts, and unfamiliar transactions in your bank.

### 6. Know when to get help

If you handle client data, if money is involved, or if the problem keeps coming back, get professional help. In some cases the safest fix is a clean reinstall, with your files restored from a backup made before the problem started.

## Prevention is easier than recovery

- Keep your operating system and apps updated
- Use a password manager and unique passwords
- Turn on MFA everywhere it's offered
- Keep [backups that actually work](/blog/the-3-2-1-backup-rule)

## Need help now?

Our [remote IT support](/it-support) can check your computer, clean it up and explain what happened. [Get in touch](/contact).
MD,
    ],
    [
        'slug'     => 'microsoft-365-vs-google-workspace',
        'cover'    => 'cloud',
        'title'    => 'Microsoft 365 vs Google Workspace: Which Is Right for Your Small Business?',
        'meta_title' => 'Microsoft 365 vs Google Workspace for Small Business | Enoma',
        'meta_description' => 'A practical comparison of Microsoft 365 and Google Workspace for small businesses: email, documents, collaboration, security and which suits the way you work.',
        'category' => 'cloud',
        'tags'     => ['Microsoft 365', 'Google Workspace', 'Business email'],
        'featured' => false,
        'excerpt'  => 'Both give you professional email, file storage and online documents. The right choice depends on how your team works. Here is a practical, jargon-free comparison.',
        'body'     => <<<'MD'
If you're setting up professional email and shared files for your business, you'll almost certainly choose between **Microsoft 365** and **Google Workspace**. Both are reliable and secure when set up properly. The better choice depends on how you and your team like to work.

## What you get with both

- Professional email on your own domain (you@yourbusiness.com)
- Online file storage and sharing
- Calendars and video meetings
- Documents, spreadsheets and presentations
- Admin controls and security settings, including multi-factor authentication

## Microsoft 365 suits you if…

- Your team already lives in **Word, Excel and Outlook**
- You exchange complex documents or spreadsheets with clients who use Microsoft Office
- You want **desktop apps** as well as web versions
- You plan to use **Teams** for chat and meetings
- You may need more detailed device and security management as you grow

## Google Workspace suits you if…

- Your team works mostly **in the browser** and on phones
- You value **real-time collaboration**: several people editing the same document at once
- You want a **simple, clean admin experience**
- You already use Gmail and Google Drive personally and like how they work

## Things people often overlook

- **Migration:** moving existing email and files takes planning. Do it once, carefully.
- **Security settings:** neither is secure "out of the box" for every business. Turn on MFA, review sharing settings and separate admin accounts. See [how to secure your business email](/blog/how-to-secure-your-business-email).
- **Backups:** cloud services keep your data available, but a separate backup protects you from accidental deletion and ransomware.
- **Plans change:** check each provider's current plans and pricing on their official websites before you decide.

## The honest answer

If your business runs on Office documents, choose Microsoft 365. If you value simplicity and live collaboration, Google Workspace is hard to beat. Either way, the setup matters more than the brand.

## Want help choosing or switching?

We set up, migrate and secure Microsoft 365 and Google Workspace for small businesses. [See our cloud services](/cloud-services) or [book a consultation](/book-a-consultation).
MD,
    ],
    [
        'slug'     => 'signs-your-website-needs-a-redesign',
        'cover'    => 'webdev',
        'title'    => '7 Signs Your Business Website Needs a Redesign',
        'meta_title' => '7 Signs Your Business Website Needs a Redesign | Enoma',
        'meta_description' => 'Is your website costing you customers? Seven clear signs your business website needs a redesign, from mobile problems to slow pages and unclear messaging.',
        'category' => 'web-development',
        'tags'     => ['Website redesign', 'Small business', 'Mobile'],
        'featured' => false,
        'excerpt'  => 'Your website is often a customer’s first impression. If it’s hard to use on a phone, slow to load or unclear about what you do, it may be quietly sending people elsewhere.',
        'body'     => <<<'MD'
Your website works for you around the clock, or against you. These seven signs suggest it's time for a refresh.

## 1. It's hard to use on a phone

Many visitors will find you on their phone. If they have to pinch and zoom, if buttons are tiny, or if menus don't work, they'll leave. A modern website adjusts to every screen size.

## 2. It's slow to load

People rarely wait for slow pages. Large images, outdated code and cheap hosting are common causes. Speed also affects how easily search engines can show your site.

## 3. Visitors can't tell what you do in five seconds

When someone lands on your homepage, they should immediately understand **what you offer, who it's for and what to do next**. If your headline is vague, your message needs work, not just your design.

## 4. It's hard to contact you

Your phone number, email or contact form should be one tap away on every page. If people have to hunt for it, many won't bother.

## 5. It looks out of date

Old designs, low-quality images and copyright notices from years ago make a business look inactive. Visitors may wonder whether you're still trading.

## 6. You can't update it yourself

If changing a price or adding a photo means calling a developer, your site will fall behind. A good website lets you make everyday updates easily.

## 7. It isn't secure

If your address doesn't start with **https://** and show a padlock, browsers may warn visitors that your site isn't secure. Outdated plugins and software are also a common way in for attackers.

## What a redesign should give you

- A clear message and obvious next steps
- A layout that works on every device
- Fast loading pages
- Security built in from day one
- Easy updates and basic SEO

See also: [what every small business website needs](/blog/small-business-website-essentials).

## Thinking about a refresh?

We redesign outdated websites with clearer structure, modern layouts and mobile-friendly pages. [See our web development services](/web-development) or [request a quote](/get-a-quote).
MD,
    ],
    [
        'slug'     => 'how-to-create-strong-passwords',
        'cover'    => 'post-mfa',
        'title'    => 'How to Create Strong Passwords You Can Actually Remember',
        'meta_title' => 'How to Create Strong Passwords You Can Remember | Enoma',
        'meta_description' => 'Simple, practical advice on creating strong passwords and passphrases, using a password manager and keeping every account safe without memorising dozens of logins.',
        'category' => 'training',
        'tags'     => ['Passwords', 'Password manager', 'Account security'],
        'featured' => false,
        'excerpt'  => 'Long beats complicated, unique beats clever, and a password manager beats a notebook. Here’s a simple approach to passwords that works for the whole family.',
        'body'     => <<<'MD'
Most people know they should use strong passwords. The problem is remembering dozens of them. Here's a simple approach that's both safer and easier.

## Length beats complexity

A long password is harder to crack than a short, complicated one. Instead of something like `P@55w0rd!`, use a **passphrase**: several random, unrelated words strung together.

For example: `copper-lantern-river-biscuit`

It's long, easy to type and much easier to remember than random symbols.

## Never reuse passwords

When one website is breached, attackers try the same email and password on other sites. If you reuse a password, one breach can open many doors. **Every account needs its own password.**

## Let a password manager remember them

A password manager stores all your passwords in an encrypted vault, protected by one strong main password. It can:

- Create long, unique passwords for every site
- Fill them in for you on your computer and phone
- Warn you about weak or reused passwords

Many reputable password managers are available, and some browsers and phones include one. Choose one, and protect it with a strong passphrase and multi-factor authentication.

## Add a second layer

Even the best password can be stolen through a convincing fake login page. Turning on [multi-factor authentication](/blog/multi-factor-authentication-explained) means a stolen password alone isn't enough.

## Where to start today

1. Pick a password manager and set it up
2. Create a strong passphrase for it, and for your main email account
3. Change passwords you've reused, starting with email and banking
4. Turn on MFA for your important accounts

## Help for teams and families

We run friendly, plain-language training on passwords, phishing and staying safe online, for teams, seniors and families. [See our training](/training) or [book a session](/book-a-consultation).
MD,
    ],
    [
        'slug'     => 'what-to-know-before-building-a-business-website',
        'cover'    => 'consulting',
        'title'    => 'What to Know Before You Build a Business Website',
        'meta_title' => 'What to Know Before Building a Business Website | Enoma',
        'meta_description' => 'Planning a business website? What to decide first: goals, pages, domain and hosting, content, budget factors and ongoing care, explained in plain language.',
        'category' => 'web-development',
        'tags'     => ['Website planning', 'Domain', 'Hosting'],
        'featured' => false,
        'excerpt'  => 'A little planning saves a lot of time and money. Here’s what to decide before you build: your goal, your pages, your domain and hosting, your content and who will look after it.',
        'body'     => <<<'MD'
A good website starts long before anyone writes code. These questions will save you time, money and frustration, whoever builds your site.

## 1. What should the website achieve?

Pick one main goal: more enquiries, more bookings, online sales or simply credibility when people look you up. Everything else, from the homepage message to the buttons, should support that goal.

## 2. Which pages do you really need?

Most small business websites need fewer pages than people think:

- **Home:** what you do, who it's for, what to do next
- **Services:** clear descriptions and benefits
- **About:** the people behind the business
- **Contact:** a form, email, phone and, if relevant, location or service area

Add a blog or resources later if you plan to keep them updated.

## 3. Domain name and hosting

Your **domain** is your address (yourbusiness.com). Your **hosting** is where the website lives. Choose a short, easy-to-spell domain, register it in your own name, and keep the login details safe. Reliable hosting with SSL (the padlock) is essential.

## 4. Content is usually the slowest part

Text, photos, prices and service descriptions often take longer than the design. Start gathering them early. Real photos of you and your work build more trust than generic stock images.

## 5. What affects the cost

Rather than a single price, website budgets depend on:

- The number of pages and how much content needs writing
- Features such as booking, online payments or a members area
- Whether you need help with domain, hosting and business email
- Ongoing maintenance, updates and backups

Ask any provider to explain exactly what's included.

## 6. Who will look after it?

Websites need updates, backups and occasional changes. Decide whether you'll handle content updates yourself and who will take care of security and maintenance.

## Ready to start?

We build fast, secure, mobile-ready websites for small businesses and professionals, and explain every step. [See our web development services](/web-development) or [request a quote](/get-a-quote).
MD,
    ],
];
