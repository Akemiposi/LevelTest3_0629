document.addEventListener("DOMContentLoaded", () => {
  // 翻訳機能（任意）
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

  // answerBtnクリック時に選択状態・スコア更新
 document.querySelectorAll(".buttonGroup").forEach((group) => {
  const name = group.getAttribute("data-question");
  const buttons = group.querySelectorAll(".answerBtn");

  // 🔧 groupの中ではなく、document全体から input を探すように変更
  const hiddenInput = document.querySelector(`input[name="${name}"]`);

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      buttons.forEach((btn) => btn.classList.remove("selected"));
      button.classList.add("selected");

      if (hiddenInput) {
        hiddenInput.value = button.dataset.value || "0";
      } else {
        console.warn(`input[name="${name}"] が見つかりません`, group);
      }

      updateScore(); // スコア更新
    });
  });
});

  // ひらがな・カタカナ表クリック処理
  document.querySelectorAll(".toggle-cell").forEach((cell) => {
    const originalChar = cell.dataset.char || "";
    const isBlank = cell.textContent.trim() === "";

    if (originalChar && isBlank) {
      // クリック可能マス
      cell.addEventListener("click", () => {
        const isSelected = cell.classList.contains("selected");

        if (isSelected) {
          cell.classList.remove("selected");
          cell.classList.add("unselected");
          cell.textContent = "　";
        } else {
          cell.classList.remove("unselected");
          cell.classList.add("selected");
          cell.textContent = originalChar;
        }

        collectKanaSelections();
        updateScore();
      });
    } else {
      cell.classList.add("readonly");
    }
  });
});

// ひらがな・カタカナ選択を収集 → hiddenに反映（あいうえお順＋小文字除外）
function collectKanaSelections() {
  const kanaTypes = ['hiragana', 'katakana'];
  
  const gojuonOrder = (
    "あいうえおかきくけこさしすせそたちつてとなにぬねのはひふへほまみむめもやゆよらりるれろわをんアイウエオカキクケコサシスセソタチツテト" +
    "ナニヌネノハヒフヘホマミムメモヤユヨラリルレロワヲン"
  ).split("");

  const gojuonSort = (a, b) => gojuonOrder.indexOf(a) - gojuonOrder.indexOf(b);

  kanaTypes.forEach(type => {
    const selected = [];
    const unselected = [];

    document.querySelectorAll(`.${type}-table .toggle-cell`).forEach(cell => {
      if (cell.classList.contains("readonly")) return;

      const char = cell.dataset.char;
      const text = cell.textContent.trim();

      if (text === char && cell.classList.contains("selected")) {
        selected.push(char);
      } else {
        unselected.push(char);
      }
    });

    selected.sort(gojuonSort);
    unselected.sort(gojuonSort);

    document.getElementById(`selected_${type}`).value = selected.join(',');
    document.getElementById(`unselected_${type}`).value = unselected.join(',');
  });
}

// スコア再計算と表示更新
function updateScore() {
  const setText = (id, val) => {
    const el = document.getElementById(id);
    if (el) el.innerText = val;
  };

  const getScoreSum = (prefix, count) => {
    let sum = 0;
    for (let i = 1; i <= count; i++) {
      const input = document.getElementById(`${prefix}_${i}`);
      const val = parseInt(input?.value || "0", 10);
      sum += val;
    }
    return sum;
  };

  const q0_1 = getScoreSum("q0_1", 7);
  const q0_2 = getScoreSum("q0_2", 10);
  const q0_3 = getScoreSum("q0_3", 3);

  document.getElementById("q0_1_score").value = q0_1;
  document.getElementById("q0_2_score").value = q0_2;
  document.getElementById("q0_3_score").value = q0_3;
  setText("q0_1_score_display", q0_1);
  setText("q0_2_score_display", q0_2);
  setText("q0_3_score_display", q0_3);


  // ひらがな・カタカナスコア
  const countSelectedKana = (id) => {
    const val = document.getElementById(id)?.value || "";
    return val.split(',').filter(s => s.trim() !== "").length;
  };
  const hira = countSelectedKana("selected_hiragana");
  const kata = countSelectedKana("selected_katakana");
  const q0_4 = hira + kata;

  document.getElementById("hiragana_score").value = hira;
  document.getElementById("katakana_score").value = kata;
  document.getElementById("q0_4_score").value = q0_4;

  // スコア表示
 
  setText("q0_4_score_display", q0_4);
  setText("hiragana_count", hira);
  setText("katakana_count", kata);

  // 総合スコア
  const total = q0_1 + q0_2 + q0_3 + q0_4;
  document.getElementById("total_score").value = total;
  setText("total_score_display", total);
}

// 送信前処理
function handleSubmit() {
  updateScore();
  return true;
}

