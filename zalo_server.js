const express = require("express");
const bodyParser = require("body-parser");

const app = express();
app.use(bodyParser.json());

// Access Token OA (lấy trong OA Test hoặc OA chính thức)
const ACCESS_TOKEN = "YOUR_ACCESS_TOKEN";

// Webhook nhận sự kiện từ Zalo
app.post("/webhook/zalo", (req, res) => {
  console.log("Webhook data:", JSON.stringify(req.body, null, 2));

  if (req.body.event_name === "user_send_text") {
    const userId = req.body.sender.id;
    const message = req.body.message.text;
    console.log(`User ${userId} gửi: ${message}`);
    // Sau này gọi API OA để trả lời
  }

  res.sendStatus(200);
});

app.get("/", (req, res) => {
  res.send("Zalo Bot Demo trong shopbanhang!");
});

const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Zalo bot server chạy tại http://localhost:${PORT}`);
});
