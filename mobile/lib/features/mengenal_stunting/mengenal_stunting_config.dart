class MengenalStuntingConfig {
  MengenalStuntingConfig._();

  static const menuSlug = 'mengenal-stunting';

  static String? itemLogoAsset(String slug) {
    switch (slug) {
      case 'pengertian':
        return 'assets/images/mengenal_stunting/pengertian_logo.png';
      case 'ciri-ciri':
        return 'assets/images/mengenal_stunting/ciri_ciri_logo.png';
      case 'penyebab':
        return 'assets/images/mengenal_stunting/penyebab_logo.png';
      case 'siapa-yang-berisiko':
        return 'assets/images/mengenal_stunting/siapa_yang_berisiko_logo.png';
      case 'dampak':
        return 'assets/images/mengenal_stunting/dampak_logo.png';
      default:
        return null;
    }
  }
}
