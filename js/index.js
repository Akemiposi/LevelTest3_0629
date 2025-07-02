

//geminiでの言語セレクト翻訳
const sourceText =
  "これから日本語で質問をします。聞いて答えてください。もし聞かれている内容がわからない時は、「わからない」と言うか、首を横に振ってください。";

document
  .getElementById("languageSelect")
  .addEventListener("change", function () {
    const targetLang = this.value;

    fetch("https://tts-node-71od.onrender.com/translate", {
      method: "POST",
      body: JSON.stringify({
        q: sourceText,
        target: targetLang,
      }),
      headers: {
        "Content-Type": "application/json",
      },
    })
      .then((res) => res.json())
      .then((data) => {
        const translated = data.translatedText;
        document.getElementById("translated-instruction").innerText =
          translated;
      })
      .catch((err) => {
        console.error("翻訳失敗", err);
      });
  });

document.querySelectorAll(".buttonGroup").forEach((group) => {
  const name = group.getAttribute("data-question");
  const buttons = group.querySelectorAll(".answerBtn");
  const hiddenInput = group.parentElement.querySelector(
    `input[name="${name}"]`
  );

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      buttons.forEach((btn) => btn.classList.remove("selected"));
      button.classList.add("selected");
      hiddenInput.value = button.dataset.value;
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  // 読み上げボタンに対応
  for (let i = 1; i <= 7; i++) {
    const readButton = document.getElementById(`read-q${i}`);
    if (readButton) {
      readButton.addEventListener("click", () => {
        const textElement = document.getElementById(`q${i}-text`);
        if (textElement) {
          speakWithGoogle(textElement.textContent.trim());
        }
      });
    }
  }
});

// 読み上げボタン処理（DOMContentLoaded内）
document.addEventListener("DOMContentLoaded", () => {
  for (let i = 1; i <= 7; i++) {
    const readButton = document.getElementById(`read-q${i}`);
    if (readButton) {
      readButton.addEventListener("click", () => {
        const textElement = document.getElementById(`q${i}-text`);
        if (textElement) {
          playTextWithGoogleTTS(textElement.textContent.trim());
        }
      });
    }
  }
});

// Google Cloud Text-to-Speech にリクエストして音声再生（Blob版）
async function speakWithGoogle(text) {
  const audioUrl = `https://tts-node-71od.onrender.com/speak?s=${encodeURIComponent(text)}`;
  const audio = new Audio(audioUrl);

  audio.onloadeddata = () => {
    audio.play().catch((err) => {
      console.error("🎵 再生エラー:", err);
    });
  };

  audio.onerror = (e) => {
    console.error("🔊 音声読み込みエラー:", e);
  };
}
