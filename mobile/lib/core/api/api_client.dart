import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../auth/token_store.dart';
import '../config/app_config.dart';
import '../utils/json.dart';
import 'api_exception.dart';

final appConfigProvider = Provider<AppConfig>((ref) => AppConfig.fromEnvironment());

/// Thin wrapper over Dio that talks to `/api/v1`, attaches the bearer token,
/// and converts failures into [ApiException]s.
class ApiClient {
  ApiClient(this._dio);

  final Dio _dio;

  Future<Json> get(String path, {Map<String, dynamic>? query}) =>
      _send(() => _dio.get<dynamic>(path, queryParameters: _clean(query)));

  Future<Json> post(String path, {Object? data}) => _send(() => _dio.post<dynamic>(path, data: data));

  Future<Json> _send(Future<Response<dynamic>> Function() request) async {
    try {
      final response = await request();
      return asMap(response.data);
    } on DioException catch (error) {
      throw ApiException.fromDio(error);
    }
  }

  static Map<String, dynamic>? _clean(Map<String, dynamic>? query) {
    if (query == null) return null;
    return Map.of(query)..removeWhere((_, value) => value == null || (value is String && value.isEmpty));
  }
}

Dio buildDio({
  required AppConfig config,
  required String? Function() readToken,
  required void Function() onUnauthorized,
}) {
  final dio = Dio(
    BaseOptions(
      baseUrl: '${config.apiV1Url}/',
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 30),
      headers: {'Accept': 'application/json'},
      contentType: Headers.jsonContentType,
      responseType: ResponseType.json,
    ),
  );

  dio.interceptors.add(
    InterceptorsWrapper(
      onRequest: (options, handler) {
        final token = readToken();
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        handler.next(options);
      },
      onError: (error, handler) {
        final sentToken = error.requestOptions.headers.containsKey('Authorization');
        if (error.response?.statusCode == 401 && sentToken) {
          onUnauthorized();
        }
        handler.next(error);
      },
    ),
  );
  return dio;
}

final apiClientProvider = Provider<ApiClient>((ref) {
  final dio = buildDio(
    config: ref.watch(appConfigProvider),
    readToken: () => ref.read(authTokenProvider),
    // A 401 on an authenticated request means the token expired or was
    // revoked: drop it so the app returns to the signed-out state.
    onUnauthorized: () => ref.read(authTokenProvider.notifier).clear(),
  );
  ref.onDispose(dio.close);
  return ApiClient(dio);
});
