class UserModel {
  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.phone,
    required this.roles,
    this.profilePhotoUrl,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final List<String> roles;
  final String? profilePhotoUrl;

  bool get canDeleteAccount =>
      roles.contains('kader') || roles.contains('user');

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final profilePhotoUrl = json['profile_photo_url'] as String?;

    return UserModel(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      phone: json['phone'] as String?,
      roles: (json['roles'] as List<dynamic>? ?? [])
          .map((role) => role.toString())
          .toList(),
      profilePhotoUrl: profilePhotoUrl != null && profilePhotoUrl.trim().isNotEmpty
          ? profilePhotoUrl.trim()
          : null,
    );
  }
}
