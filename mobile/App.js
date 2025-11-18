/**
 * ICHRI Tunisia Mobile - Main App
 */

import React from 'react';
import { StatusBar } from 'react-native';
import Toast from 'react-native-toast-message';
import AppNavigator from './src/navigation/AppNavigator';
import { Colors } from './src/config/theme';

const App = () => {
  return (
    <>
      <StatusBar
        barStyle="light-content"
        backgroundColor={Colors.primary}
      />
      <AppNavigator />
      <Toast />
    </>
  );
};

export default App;
