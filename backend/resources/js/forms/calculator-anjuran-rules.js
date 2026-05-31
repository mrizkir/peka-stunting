/**
 * Form builder aturan anjuran kalkulator (CMS edukasi).
 *
 * @param {object} initial
 * @param {Array<object>} [initial.rules]
 */
export function createCalculatorAnjuranRules(initial = {}) {
  const seedRules = Array.isArray(initial.rules) ? initial.rules : [];
  const defaultMetric = initial.defaultMetric ?? 'bmi';
  const defaultIndicator = initial.defaultIndicator ?? 'height_for_age';

  return {
    rules: seedRules.map((rule, index) => ({
      sort_order: Number(rule.sort_order ?? index + 1),
      metric: rule.metric ?? defaultMetric,
      indicator: rule.indicator ?? defaultIndicator,
      threshold: rule.threshold ?? '',
      operator: rule.operator ?? 'gt',
      is_default: Boolean(rule.is_default),
      label: rule.label ?? '',
      slug: rule.slug ?? '',
      anjuran: rule.anjuran ?? '',
    })),

    addRule() {
      const next = this.rules.length + 1;
      this.rules.push({
        sort_order: next,
        metric: defaultMetric,
        indicator: defaultIndicator,
        threshold: '',
        operator: 'gt',
        is_default: false,
        label: '',
        slug: '',
        anjuran: '',
      });
    },

    removeRule(index) {
      if (this.rules.length <= 1) {
        return;
      }
      this.rules.splice(index, 1);
    },

    moveUp(index) {
      if (index <= 0) {
        return;
      }
      const item = this.rules.splice(index, 1)[0];
      this.rules.splice(index - 1, 0, item);
    },

    moveDown(index) {
      if (index >= this.rules.length - 1) {
        return;
      }
      const item = this.rules.splice(index, 1)[0];
      this.rules.splice(index + 1, 0, item);
    },
  };
}
