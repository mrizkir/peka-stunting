/**
 * Form builder kuesioner kalkulator (CMS edukasi).
 *
 * @param {object} initial
 * @param {number} [initial.risk_yes_threshold]
 * @param {Array<{id: string, text: string}>} [initial.questions]
 */
export function createCalculatorQuestionnaire(initial = {}) {
  const seedQuestions = Array.isArray(initial.questions) ? initial.questions : [];

  return {
    riskYesThreshold: Number(initial.risk_yes_threshold ?? 3) || 3,
    questions: seedQuestions.map((question, index) => ({
      id: question.id || `pertanyaan_${index + 1}`,
      text: question.text ?? '',
    })),

    addQuestion() {
      const next = this.questions.length + 1;
      this.questions.push({
        id: `pertanyaan_${next}`,
        text: '',
      });
    },

    removeQuestion(index) {
      if (this.questions.length <= 1) {
        return;
      }
      this.questions.splice(index, 1);
    },

    moveUp(index) {
      if (index <= 0) {
        return;
      }
      const item = this.questions.splice(index, 1)[0];
      this.questions.splice(index - 1, 0, item);
    },

    moveDown(index) {
      if (index >= this.questions.length - 1) {
        return;
      }
      const item = this.questions.splice(index, 1)[0];
      this.questions.splice(index + 1, 0, item);
    },
  };
}
