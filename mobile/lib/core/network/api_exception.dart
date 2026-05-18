class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.errors});

  final String message;
  final int? statusCode;
  final dynamic errors;

  String get displayMessage {
    if (errors is Map) {
      for (final value in (errors as Map).values) {
        if (value is List && value.isNotEmpty) {
          return value.first.toString();
        }
        if (value is String && value.isNotEmpty) {
          return value;
        }
      }
    }
    return message;
  }

  @override
  String toString() => displayMessage;
}
