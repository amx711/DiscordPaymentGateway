const { Client, GatewayIntentBits, ChannelType, EmbedBuilder, PermissionsBitField } = require("discord.js");
const fs = require("fs");
const mysql = require('mysql2');
const axios = require('axios');
const config = require("./config.json");

const client = new Client({
  intents: [
    GatewayIntentBits.Guilds,
    GatewayIntentBits.GuildMessages,
    GatewayIntentBits.MessageContent
  ]
});

const pool = mysql.createPool({
  host: config.mysql.host,
  user: config.mysql.user,
  password: config.mysql.password,
  database: config.mysql.database
});


const activeInvoices = new Set();

client.once("ready", () => {
  console.clear();
  console.log(`✅ Logged in as ${client.user.tag}`);
  console.log(`Amx711 - Dev`);

  setInterval(() => {
    const raw = fs.readFileSync(config.invoicePath);
    const data = JSON.parse(raw);

    data.forEach(async (entry, index) => {
      if (entry.status === "not paid" && !activeInvoices.has(entry.invoice_id)) {
        activeInvoices.add(entry.invoice_id);

        const guild = client.guilds.cache.first();
        const buyer = await guild.members.fetch(entry.user_id).catch(() => null);
        if (!buyer) return;

        const channel = await guild.channels.create({
          name: `${buyer.user.username}-process`,
          type: ChannelType.GuildText,
          parent: config.categoryId,
          permissionOverwrites: [
            {
              id: guild.roles.everyone,
              deny: [PermissionsBitField.Flags.ViewChannel],
            },
            {
              id: buyer.id,
              allow: [PermissionsBitField.Flags.ViewChannel, PermissionsBitField.Flags.SendMessages],
            },
            {
              id: client.user.id,
              allow: [PermissionsBitField.Flags.ViewChannel, PermissionsBitField.Flags.SendMessages],
            }
          ]
        });

        const embed = new EmbedBuilder()
          .setColor("Blue")
          .setTitle("💵 تأكيد الدفع")
          .setDescription(
            `اهلا بك عزيزي، كل ما عليك هو نسخ الأمر التالي وإرساله:\n\n\`\`\`\nc ${config.ownerId} ${entry.price}\n\`\`\``)        
          .addFields(
            { name: '📑 رقم الفاتورة', value: `${entry.invoice_id}` },
            { name: '💸 المبلغ', value: `${entry.price}`, inline: true },
            { name: '📦 المنتج', value: `${entry.product}`, inline: true },
          )
          .setFooter({ text: "⏳ معك 5 دقائق لتأكيد الدفع." });

        await channel.send({ content: `<@${buyer.id}>`, embeds: [embed] });

        let paid = false;

        const filter = (m) => {        
          if (m.author.id !== config.probotId) return false;
        
          const expected = `**💰 | ${buyer.user.username}, has transferred \`$${entry.price}\` to <@${config.ownerId}>`;
        
          return m.content.startsWith(expected);
        };
        
        const collector = channel.createMessageCollector({ filter, time: 5 * 60 * 1000 });
        
        collector.on("collect", async (msg) => {
          paid = true;
          
          const updated = JSON.parse(fs.readFileSync(config.invoicePath));
          const invoice = updated.find(e => e.invoice_id === entry.invoice_id);
          if (invoice) {
            invoice.status = "paid";
            fs.writeFileSync(config.invoicePath, JSON.stringify(updated, null, 4));
          }
        
          pool.execute(
            'UPDATE payments SET finalstatus = ? WHERE paymentid = ?',
            ['paid', entry.invoice_id],
            (err, results) => {
              if (err) {
                console.error("MySQL Error:", err);
                return;
              }
            }
          );
        
          const webhookPayload = {
            content: `${entry.invoice_id} طلب رقم`,
            embeds: [
              new EmbedBuilder()
                .setColor("Green")
                .setTitle("💸 عمليه الدفع تمت بنجاح")
                .setDescription(`رقم الفاتورة: ${entry.invoice_id}\nالمبلغ: $${entry.price}\nالمنتج: ${entry.product}\nالحالة: مدفوعة`)
                .setFooter({ text: "amx711 Dev - github/amx711" })
            ]
          };
        
          try {
            await axios.post(config.webhookURL, webhookPayload);
          } catch (err) {
            console.error('Error sending webhook:', err);
          }
        
          channel.send({
            content: `<@${buyer.id}>`,
            embeds: [
              new EmbedBuilder()
                .setColor("Green")
                .setDescription("**✅ لقد تم معالجة عملية الدفع بنجاح! شكراً لك على اختيارنا!\nسوف يتم حذف الروم خلال دقيقتين.**")
                .setFooter({ text: "amx711 Dev - github/amx711" })
            ]
          });
        
          setTimeout(() => {
            channel.delete().catch(() => {});
          }, 120000);
        });
        

        collector.on("end", () => {
          if (!paid) {
            const updated = JSON.parse(fs.readFileSync(config.invoicePath));
            const filtered = updated.filter(e => e.invoice_id !== entry.invoice_id);
            fs.writeFileSync(config.invoicePath, JSON.stringify(filtered, null, 4));

            channel.send({
              content: `<@${buyer.id}>`,
              embeds: [
                new EmbedBuilder()
                  .setColor("Red")
                  .setDescription("⛔ لم يتم تأكيد الدفع.\nسوف يتم حذف الفاتورة والروم خلال 60 ثانية.")
                  .setFooter({ text: "amx711 Dev - github/amx711" })
              ]
            });

            setTimeout(() => {
              channel.delete().catch(() => {});
            }, 60000);
          }
        });
      }
    });
  }, 5);
});

client.login(config.token);
