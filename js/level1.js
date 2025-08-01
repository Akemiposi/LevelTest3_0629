console.log("level1.js 読み込み成功");

document.addEventListener("DOMContentLoaded", () => {

  // ==============================
  // 翻訳機能
  // ==============================
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

  // ==============================
  // 安全に値をセットする関数
  // ==============================
  const setValue = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.value = val;
  };

  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
  };

  // ==============================
  // スコア計算関数
  // ==============================
  function calculateAllScores() {
    const getSum = (prefix, count) => {
      let total = 0;
      for (let i = 1; i <= count; i++) {
        const hidden = document.querySelector(`input[name="${prefix}_${i}"]`);
        if (hidden) {
          const val = parseInt(hidden.value || 0, 10);
          total += isNaN(val) ? 0 : val;
        }
      }
      return total;
    };

    const q1_1 = getSum("q1_1", 6);
    const q1_2 = getSum("q1_2", 4) + getSum("q1_2_5", 5);
    const q1_3 = getSum("q1_3", 5);
    const ondoku = parseInt(
      document.querySelector(`input[name="q1_3_ondoku"]`)?.value || 0,
      10
    );

    const q1_3_score = q1_3 + (isNaN(ondoku) ? 0 : ondoku);
    const total = q1_1 + q1_2 + q1_3_score;

    // hiddenに反映（存在チェック付き）
    setValue("q1_1_score", q1_1);
    setValue("q1_2_score", q1_2);
    setValue("q1_3_score", q1_3_score);
    setValue("q1_total_score", total);

    // 表示に反映（存在チェック付き）
    setText("q1_1_score_display", q1_1);
    setText("q1_2_score_display", q1_2);
    setText("q1_3_score_display", q1_3_score);
    setText("q1_total_score_display", total);

    console.log(`結果: Q1-1=${q1_1}, Q1-2=${q1_2}, Q1-3=${q1_3}, 音読=${ondoku}, 合計=${total}`);
  }

  // ==============================
  // ボタングループ（.answerBtn）クリック時
  // ==============================
  document.querySelectorAll(".buttonGroup").forEach(group => {
    const name = group.getAttribute("data-question");
    const hiddenInput = document.querySelector(`input[name="${name}"]`);
    const buttons = group.querySelectorAll(".answerBtn");

    buttons.forEach(button => {
      button.addEventListener("click", () => {
        // ボタン見た目
        buttons.forEach(btn => btn.classList.remove("selected"));
        button.classList.add("selected");

        // hidden更新
        if (hiddenInput) {
          hiddenInput.value = button.dataset.value || "0";
        }

        calculateAllScores();
      });
    });
  });

  // ==============================
  // ラジオ選択時にhidden更新
  // ==============================
  document.querySelectorAll('input[type="radio"]').forEach(radio => {
    radio.addEventListener("change", () => {
      const name = radio.name;
      const hidden = document.querySelector(`input[name="${name}"]`);
      if (hidden) {
        hidden.value = radio.value;
      }
      calculateAllScores();
    });
  });

  // ==============================
  // 数値入力時
  // ==============================
  document.querySelectorAll('input[type="number"]').forEach(el => {
    el.addEventListener("input", function () {
      this.value = this.value || 0;
      calculateAllScores();
    });
  });

  // ==============================
  // 再計算ボタン
  // ==============================
  window.updateScore = calculateAllScores;

  // ==============================
  // 送信前保険
  // ==============================
  const form = document.querySelector("form");
  if (form) {
    form.addEventListener("submit", calculateAllScores);
  }

  // ==============================
  // 初期計算
  // ==============================
  calculateAllScores();
});
