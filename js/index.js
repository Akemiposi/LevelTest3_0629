document.addEventListener("DOMContentLoaded", () => {
  const languageSelect = document.getElementById("languageSelect");
  if (languageSelect) {
    languageSelect.addEventListener("change", function () {
      const targetLang = this.value;

      document.querySelectorAll(".translated-instruction").forEach((el) => {
        const sourceText = el.innerText.trim();
        if (!sourceText) return;

        fetch("gemini.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ text: sourceText, target: targetLang }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.translatedText) el.innerText = data.translatedText;
          })
          .catch((err) => console.error("翻訳失敗", err));
      });
    });
  }

  // ボタン選択処理（聞く・読む・書く）
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
        updateScore(); // スコア更新
      });
    });
  });

  // ひらがな・カタカナマス処理
  document.querySelectorAll(".toggle-cell").forEach((cell) => {
    const originalChar = cell.dataset.char || "";
    if (cell.textContent.trim() === "") {
      cell.addEventListener("click", () => {
        cell.classList.toggle("selected");
        cell.textContent = cell.classList.contains("selected")
          ? originalChar
          : "　";
        updateScore();
      });
    }
  });

  // 読み上げボタン対応（ID: read-q1〜read-q7）
  for (let i = 1; i <= 7; i++) {
    const readButton = document.getElementById(`read-q${i}`);
    if (readButton) {
      readButton.addEventListener("click", () => {
        const textElement = document.getElementById(`q${i}-text`);
        if (textElement) speakWithGoogle(textElement.textContent.trim());
      });
    }
  }
});

// 音声読み上げ（Google TTS）
function speakWithGoogle(text) {
  fetch("tts.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ text }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        new Audio(data.file + "?t=" + Date.now()).play();
      } else {
        console.error("読み上げ失敗:", data.error);
      }
    })
    .catch((err) => {
      console.error("通信エラー:", err);
    });
}

// スコア計算（すべて自動）
function updateScore() {
  const hiraOrder = 'あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをん'.split('');
  const kataOrder = 'アイウエオカキクケコサシスセソタチツテトナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲンー'.split('');

  // 値取得と配列化（空要素除外）
  const getList = (id) => {
    const val = document.getElementById(id)?.value || '';
    return val.split(',').map(s => s.trim()).filter(s => s);
  };

  const selectedHiragana = getList("selected_hiragana").sort((a, b) => hiraOrder.indexOf(a) - hiraOrder.indexOf(b));
  const unselectedHiragana = getList("unselected_hiragana").sort((a, b) => hiraOrder.indexOf(a) - hiraOrder.indexOf(b));
  const selectedKatakana = getList("selected_katakana").sort((a, b) => kataOrder.indexOf(a) - kataOrder.indexOf(b));
  const unselectedKatakana = getList("unselected_katakana").sort((a, b) => kataOrder.indexOf(a) - kataOrder.indexOf(b));

  // 値をDOMに戻す（並び替え反映）
  const setValue = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.value = value;
  };

  setValue("selected_hiragana", selectedHiragana.join(","));
  setValue("unselected_hiragana", unselectedHiragana.join(","));
  setValue("selected_katakana", selectedKatakana.join(","));
  setValue("unselected_katakana", unselectedKatakana.join(","));

  // スコア更新
  setValue("hiragana_score", selectedHiragana.length);
  setValue("katakana_score", selectedKatakana.length);
  setValue("q0_4_score", selectedHiragana.length + selectedKatakana.length);

  // 総合スコア計算
  const getScore = (id) => parseInt(document.getElementById(id)?.value || "0", 10);
  const total = getScore("q0_1_score") + getScore("q0_2_score") + getScore("q0_3_score") + getScore("q0_4_score");
  setValue("total_score", total);

  // 表示用spanがあれば更新
  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.innerText = value;
  };
  setText("hiragana_count", selectedHiragana.length);
  setText("katakana_count", selectedKatakana.length);
}



function collectKanaSelections() {
  const kanaTypes = ['hiragana', 'katakana'];
  kanaTypes.forEach(type => {
    const selected = [];
    const unselected = [];

    document.querySelectorAll(`.${type}-table .toggle-cell`).forEach(cell => {
      const char = cell.dataset.char;
      const isSelected = cell.classList.contains('selected');
      if (isSelected) {
        selected.push(char);
      } else {
        unselected.push(char);
      }
    });

    document.getElementById(`selected_${type}`).value = selected.join(',');
    document.getElementById(`unselected_${type}`).value = unselected.join(',');
  });
}


// フォーム送信前にスコア更新
function handleSubmit() {
  calculateTotalScores(); // スコア計算
  collectKanaSelections(); // ひらがな・カタカナ選択収集
  return true;
}


