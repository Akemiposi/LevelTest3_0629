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
        // クラス名で複数の対象を取得
        document.querySelectorAll(".translated-instruction").forEach((el) => {
          el.innerText = translated;
        });
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
  const audioUrl = `https://tts-node-71od.onrender.com/speak?s=${encodeURIComponent(
    text
  )}`;
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

// スコア計算（ひらがな・カタカナそれぞれ）
function updateScore() {
  const selectedHiragana = [];
  const selectedKatakana = [];
  const unselectedHiragana = [];
  const unselectedKatakana = [];

  document.querySelectorAll(".toggle-cell").forEach((cell) => {
    const char = cell.dataset.char || "";
    const isKatakana = /[ァ-ンー]/.test(char);
    const isSelected = cell.classList.contains("selected");

    const list = isKatakana
      ? isSelected
        ? selectedKatakana
        : unselectedKatakana
      : isSelected
      ? selectedHiragana
      : unselectedHiragana;

    list.push(char);
  });

  const setValue = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value;
  };

  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
  };

  // スコアと未選択文字を反映
  setValue("hiragana_score", selectedHiragana.length);
  setValue("katakana_score", selectedKatakana.length);
  setValue("hiragana_unselected", unselectedHiragana.join(","));
  setValue("katakana_unselected", unselectedKatakana.join(","));

  // 画面表示更新
  setText("hiragana_count", selectedHiragana.length);
  setText("katakana_count", selectedKatakana.length);

  // 合計スコア反映（他セクションのスコアを加算）
  const getScore = (id) =>
    parseInt(document.getElementById(id)?.value || "0", 10);

  const totalScore =
    getScore("q0_1_score") +
    getScore("q0_2_score") +
    getScore("q0_3_score") +
    getScore("q0_4_score") +
    selectedHiragana.length +
    selectedKatakana.length;

  setValue("total_score", totalScore);
}

// 対象すべてのセルにクリック処理を設定
document.querySelectorAll(".toggle-cell").forEach((cell) => {
  const originalChar = cell.dataset.char || "";

  // 元々文字があるセルは選択不可にする
  if (cell.textContent.trim() === "") {
    cell.addEventListener("click", () => {
      cell.classList.toggle("selected");

      // 選択状態で文字を表示、非選択で全角スペースに戻す
      if (cell.classList.contains("selected")) {
        cell.textContent = originalChar;
      } else {
        cell.textContent = "　"; // 全角スペースに戻す
      }

      updateScore(); // スコア再計算
    });
  }
});

//フォーム送信時に呼び出す関数（formタグで onsubmit="return handleSubmit()" を指定）
function handleSubmit() {
  updateScore(); // スコアを明示的に反映
  return true; // フォーム送信継続
}
