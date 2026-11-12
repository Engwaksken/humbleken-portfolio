import 'package:dio/dio.dart';

import '../utils/json.dart';

/// A user-presentable API failure.
class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode, this.fieldErrors = const {}});

  final String message;
  final int? statusCode;

  /// Laravel 422 field errors: `{"field": ["message", ...]}`.
  final Map<String, List<String>> fieldErrors;

  bool get isValidation => statusCode == 422;
  bool get isUnauthorized => statusCode == 401;
  bool get isForbidden => statusCode == 403;
  bool get isNotFound => statusCode == 404;
  bool get isNetwork => statusCode == null;

  /// First error message for [field], if any.
  String? fieldError(String field) {
    final errors = fieldErrors[field];
    return (errors == null || errors.isEmpty) ? null : errors.first;
  }

  factory ApiException.fromDio(DioException error) {
    final response = error.response;
    if (response == null) {
      return switch (error.type) {
        DioExceptionType.connectionTimeout ||
        DioExceptionType.sendTimeout ||
        DioExceptionType.receiveTimeout => const ApiException('The server took too long to respond. Please try again.'),
        DioExceptionType.cancel => const ApiException('The request was cancelled.'),
        _ => const ApiException('Could not reach the server. Check your connection and try again.'),
      };
    }

    final status = response.statusCode;
    final body = asMapOrNull(response.data);
    final serverMessage = asStringOrNull(body?['message']);
    final errors = <String, List<String>>{};
    asMap(body?['errors']).forEach((key, value) => errors[key] = asStringList(value));

    return ApiException(serverMessage ?? defaultMessageFor(status), statusCode: status, fieldErrors: errors);
  }

  static String defaultMessageFor(int? status) => switch (status) {
    401 => 'Your session has ended. Please sign in again.',
    403 => 'You do not have permission to do that.',
    404 => 'We could not find what you were looking for.',
    422 => 'Please check the highlighted fields.',
    429 => 'Too many attempts. Please wait a minute and try again.',
    503 => 'The service is temporarily unavailable. Please try again shortly.',
    _ => 'Something went wrong. Please try again.',
  };

  /// Best-effort message for any error thrown from a repository.
  static String messageOf(Object error) {
    if (error is ApiException) return error.message;
    if (error is DioException) return ApiException.fromDio(error).message;
    return 'Something went wrong. Please try again.';
  }

  @override
  String toString() => 'ApiException($statusCode): $message';
}
