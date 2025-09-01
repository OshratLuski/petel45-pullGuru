# Amazon Bedrock API Provider
The Amazon Bedrock API Provider is the [Amazon Bedrock](https://aws.amazon.com/bedrock/) Moodle [AI subsystem](https://docs.moodle.org/405/en/AI_subsystem) provider, thus enabling the usage of Amazon Bedrock API on AWS and leveraging the multiple AI models it offers, including Anthropic [Claude](https://www.anthropic.com/claude).

by [Meeple](https://www.meeplesrl.it/)

**Please note**: you do need a paid subscription with AWS in order to connect this service with Moodle.

## Installation

To install this AI provider you can download the ZIP file and install from *Administration > Site administration > Plugins > Install plugins*, or you can unzip it in the `ai/provider` folder.
This provider requires Moodle LMS 4.5, the first version to include the AI subsystem.

You must provide an AWS Access Key and Secret Access Key for an AWS user with the proper permissions to use Amazon Bedrock in your AWS account. Please remind to enable the models from *Bedrock configurations > Model access* in the selected region.

You need to provide the AWS region and the [models](https://docs.aws.amazon.com/bedrock/latest/userguide/models-supported.html) to use for every specific Action.

![main settings](https://github.com/user-attachments/assets/7de1082f-ad7d-405f-a4c7-da30d206b1fe)


## Tested models with version 1.0.0

Amazon Bedrock [Foundation models](https://docs.aws.amazon.com/bedrock/latest/userguide/models-regions.html) need to be enabled from Bedrock console.

We tested US and EU regions that provide models in Amazon Bedrock:
- us-east-1
    - us.anthropic.claude-3-7-sonnet-20250219-v1:0
    - us.anthropic.claude-3-5-sonnet-20241022-v2:0
    - us.anthropic.claude-3-5-haiku-20241022-v1:0
    - anthropic.claude-3-haiku-20240307-v1:0
    - amazon.nova-canvas-v1:0 (Image)
- us-west-2
    - us.anthropic.claude-3-7-sonnet-20250219-v1:0
    - anthropic.claude-3-5-haiku-20241022-v1:0
- eu-west-1
    - eu.anthropic.claude-3-7-sonnet-20250219-v1:0
    - eu.anthropic.claude-3-5-sonnet-20240620-v1:0
    - amazon.nova-canvas-v1:0 (Image)
- eu-central-1
    - eu.anthropic.claude-3-7-sonnet-20250219-v1:0
    - anthropic.claude-3-5-sonnet-20240620-v1:0
- eu-west-3
    - eu.anthropic.claude-3-7-sonnet-20250219-v1:0
    - eu.anthropic.claude-3-5-sonnet-20240620-v1:0

**Note**: models starting with *us.* or *eu.* use a cross-region inference to increase throughput and improve resiliency by routing the model's requests across multiple AWS Regions during peak utilization bursts. In particular, with respect to GDPR compliance, *eu.* cross-region inference routes requests to European regions.

## License

2025 Meeple srl [https://www.meeplesrl.it](https://www.meeplesrl.it/)

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program. If not, see [http://www.gnu.org/licenses/](http://www.gnu.org/licenses/).

