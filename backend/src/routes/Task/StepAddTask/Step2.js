import React, { PureComponent } from 'react';
import KeywordForm from '../../../components/KeywordForm';

export default class Step2 extends PureComponent {

  render() {
    return (
      <KeywordForm 
        {...this.props}
      />
    );
  };
}