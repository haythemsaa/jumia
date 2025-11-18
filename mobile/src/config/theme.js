/**
 * ICHRI Tunisia Mobile - Theme Configuration
 */

export const Colors = {
  primary: '#667eea',
  secondary: '#764ba2',
  accent: '#FF6B9D',

  // Status colors
  success: '#4CAF50',
  warning: '#FFA000',
  error: '#F44336',
  info: '#2196F3',

  // Grayscale
  white: '#FFFFFF',
  black: '#000000',
  dark: '#212121',
  gray: '#757575',
  lightGray: '#BDBDBD',
  background: '#F5F5F5',
  border: '#E0E0E0',

  // Text
  textPrimary: '#212121',
  textSecondary: '#757575',
  textLight: '#BDBDBD',
};

export const Spacing = {
  xs: 4,
  sm: 8,
  md: 16,
  lg: 24,
  xl: 32,
  xxl: 48,
};

export const FontSizes = {
  xs: 10,
  sm: 12,
  md: 14,
  lg: 16,
  xl: 20,
  xxl: 24,
  xxxl: 32,
};

export const BorderRadius = {
  sm: 4,
  md: 8,
  lg: 12,
  xl: 16,
  full: 9999,
};

export const Shadows = {
  sm: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 1 },
    shadowOpacity: 0.18,
    shadowRadius: 1.0,
    elevation: 1,
  },
  md: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.23,
    shadowRadius: 2.62,
    elevation: 4,
  },
  lg: {
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.30,
    shadowRadius: 4.65,
    elevation: 8,
  },
};

export const GlobalStyles = {
  container: {
    flex: 1,
    backgroundColor: Colors.background,
  },
  row: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  rowBetween: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  center: {
    justifyContent: 'center',
    alignItems: 'center',
  },
  shadow: Shadows.md,
  card: {
    backgroundColor: Colors.white,
    borderRadius: BorderRadius.lg,
    padding: Spacing.md,
    ...Shadows.md,
  },
};

export default {
  Colors,
  Spacing,
  FontSizes,
  BorderRadius,
  Shadows,
  GlobalStyles
};
